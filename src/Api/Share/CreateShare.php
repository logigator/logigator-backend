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

        if ($this->getDbalQueryBuilder()
	        ->select('pk_id')
	        ->from('links')
	        ->where('links.fk_project = :project')
	        ->setParameter('project', $body->project)
            ->execute()->rowCount() > 0) {
	        throw new HttpBadRequestException($request, 'Link already exists.');
        }

        $is_public = true;
        if(isset($body->users) && count($body->users) > 0) {
            $is_public = false;
        }

        $link_address = Uuid::uuid4()->toString();
        $this->getDbalQueryBuilder()
            ->insert('links')
            ->setValue('address', '?')
            ->setValue('is_public', '?')
            ->setValue('fk_project', '?')
            ->setParameter(0, $link_address)
            ->setParameter(1, $is_public)
            ->setParameter(2, $project['pk_id'])
            ->execute();

        $link_id = $this->container->get('DbalService')->getConnection()->lastInsertId();

        $warnings = [];
        if(isset($body->users)) {
	        $warnings = $this->container->get('ProjectService')->setSharePermits($link_id,
		        $body->users,
		        isset($body->invitations) ? $body->invitations : false,
		        $user['username'],
		        $project['name'],
		        $link_address
	        );
        }

		return ApiHelper::createJsonResponse($response, ['address' => $link_address], true, $warnings);
	}
}
