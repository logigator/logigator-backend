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
        $share = $this->container->get('DbalService')->getQueryBuilder()
            ->select('link.address as "link.address", 
                link.is_public as "link.is_public",
                link.pk_id as "link.pk_id",
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
            ->where('link.address = ?')
            ->setParameter(0, $args['id'])
            ->execute()
            ->fetch();

        if(!$share)
            throw new HttpBadRequestException($request, self::ERROR_RESOURCE_NOT_FOUND);

        if($share['link.is_public'])
            return ApiHelper::createJsonResponse($response, $share, true);

        if(!$this->container->get('DbalService')->getQueryBuilder()
            ->select('fk_user, fk_link')
            ->from('link_permits')
            ->where('fk_user = ? and fk_link = ?')
            ->setParameter(0, $this->getTokenPayload()->sub)
            ->setParameter(1, $share['link.pk_id'])
            ->execute()
            ->fetch())
            throw new HttpBadRequestException($request, self::ERROR_RESOURCE_NOT_FOUND);

        return ApiHelper::createJsonResponse($response, $share, true);

    }
}
