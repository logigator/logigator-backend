<?php
namespace Logigator\Api\Share;


use Logigator\Api\ApiHelper;
use Logigator\Api\BaseController;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;
use Ramsey\Uuid\Uuid;
use Slim\Exception\HttpBadRequestException;

class CreateShare extends BaseController
{
	public function __invoke(ServerRequestInterface $request, ResponseInterface $response, array $args)
	{
        $body = $request->getParsedBody();

        $project = $this->container->get('ProjectService')->getProjectInfo($body->project, $this->getTokenPayload()->sub);
        $user = $this->container->get('UserService')->fetchUser($this->getTokenPayload()->sub);

        if(!$user)
        	throw new \Exception();

        if(!$project)
            throw new HttpBadRequestException($request, self::ERROR_RESOURCE_NOT_FOUND);

        $is_public = true;
        if(isset($body->users) && count($body->users) > 0) {
            $is_public = false;
        }

        $link_address = Uuid::uuid4()->toString();
        $this->container->get('DbalService')->getQueryBuilder()
            ->insert('links')
            ->setValue('address', '?')
            ->setValue('is_public', '?')
            ->setValue('fk_project', '?')
            ->setParameter(0, $link_address)
            ->setParameter(1, $is_public)
            ->setParameter(2, $project['pk_id'])
            ->execute();

        $link_id = $this->container->get('DbalService')->getConnection()->lastInsertId();

        $warnings = array();
        if(!$is_public) {
        	$added = [];
            foreach($body->users as $u) {
                $userData = $this->container->get('DbalService')->getQueryBuilder()
                    ->select('*')
                    ->from('users')
                    ->where('username = ? or email = ?')
                    ->setParameter(0, $u)
                    ->setParameter(1, $u)
                    ->execute()
                    ->fetch();

                if(!$userData || $userData['pk_id'] === $user['pk_id']) {
                    array_push($warnings, 'user "' . $u . '" not found."');
                    continue;
                }

                if(in_array($userData['pk_id'], $added)) {
		            array_push($warnings, 'user "' . $u . '" listed multiple times."');
		            continue;
	            }
                $added[] = $userData['pk_id'];

                $this->container->get('DbalService')->getQueryBuilder()
                    ->insert('link_permits')
                    ->setValue('fk_user', '?')
                    ->setValue('fk_link', '?')
                    ->setParameter(0, $userData['pk_id'])
                    ->setParameter(1, $link_id)
                    ->execute();

                if(isset($body->invitations) && $body->invitations === true) {
                	try {
		                $this->container->get('SmtpService')->sendMail(
		                	'noreply', [
		                		$userData['email']
			                ],
			                'Someone shared his project with you!',
			                $this->container->get('SmtpService')->loadTemplate('share-invitation.html', [
			                	'recipient' => $userData['username'],
				                'invitor' => $user['username'],
				                'project' => $project['name'],
				                'link' => '#'
			                ])
		                );
	                } catch (\Exception $e) {
                		array_push($warnings, 'Failed to send invitation to user "' . $u . '"');
	                }
                }
            }
        }

		return ApiHelper::createJsonResponse($response, ['address' => $link_address], true, $warnings);
	}
}
