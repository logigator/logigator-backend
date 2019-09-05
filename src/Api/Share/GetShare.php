<?php
namespace Logigator\Api\Share;


use Logigator\Api\ApiHelper;
use Logigator\Api\BaseController;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;
use Slim\Exception\HttpBadRequestException;

class GetShare extends BaseController
{
	public function __invoke(ServerRequestInterface $request, ResponseInterface $response, array $args)
	{
	    //TODO: shares for specific person
        $share = $this->container->get('DbalService')->getQueryBuilder()
            ->select('link.address as "link.address", 
                link.is_public as "link.is_public",
                project.pk_id as "project.id",
                project.name as "project.name",
                project.description as "project.description",
                project.symbol as "project.symbol", 
                project.last_edited as "project.last_edited",
                project.created_on as "project.created_on", 
                project.is_component as "project.is_component", 
                project.location as "project.location", 
                user.username as "user.username",
                user.profile_image as "user.profile_image"')
            ->from('links', 'link')
            ->join('link', 'projects', 'project', 'link.fk_project = project.pk_id')
            ->join('project', 'users', 'user', 'user.pk_id = project.fk_user')
            ->where('link.address = ? and link.is_public = 1')
            ->setParameter(0, $args['id'])
            ->execute()
            ->fetch();

        if(!$share)
            throw new HttpBadRequestException($request, "Share not found.");

		return ApiHelper::createJsonResponse($response, $share, true);
	}
}
