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

        if (!ApiHelper::checkRequiredArgs($body, ['project'])) {
            throw new HttpBadRequestException($request, 'Not all required args were given');
        }

        $project = $this->container->get('ProjectService')->getProjectInfo($body['project'], $this->getTokenPayload()->sub);

        if(!$project)
            throw new HttpBadRequestException($request, "Project was not found or does not belong to you.");

        $is_public = true;
        if(isset($body['users']) && is_array($body['users']) && count($body['users']) > 0) {
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
            foreach($body['users'] as $user) {
                $user = $this->container->get('DbalService')->getQueryBuilder()
                    ->select('pk_id')
                    ->from('users')
                    ->where('username = ? or email = ?')
                    ->setParameter(0, $user)
                    ->setParameter(1, $user)
                    ->execute()
                    ->fetch()['pk_id'];

                if(!$user) {
                    array_push($warnings, 'user "' . $user . '" not found."');
                    continue;
                }

                $this->container->get('DbalService')->getQueryBuilder()
                    ->insert('link_permits')
                    ->setValue('fk_user', '?')
                    ->setValue('fk_link', '?')
                    ->setParameter(0, $user)
                    ->setParameter(1, $link_id)
                    ->execute();
            }
        }

		return ApiHelper::createJsonResponse($response, ['address' => $link_address], true, $warnings);
	}
}
