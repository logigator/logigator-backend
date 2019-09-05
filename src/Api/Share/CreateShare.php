<?php
namespace Logigator\Api\Share;


use Logigator\Api\ApiHelper;
use Logigator\Api\BaseController;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;
use Slim\Exception\HttpBadRequestException;
use Slim\Exception\HttpForbiddenException;

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
            throw new HttpForbiddenException($request, "Project was not found or does not belong to you.");


        //TODO: create snapshot project


        //TODO: create link entry
        /*$share = $this->container->get('DbalService')->getQueryBuilder()
            ->insert('links')
            ->setValue('name', '?')
            ->setValue('is_component', '?')
            ->setValue('fk_user', '?')
            ->setValue('location', '?')
            ->setValue('description', '?')
            ->setValue('symbol', '?')
            ->setParameter(0, $name)
            ->setParameter(1, $isComponent)
            ->setParameter(2, $fk_user)
            ->setParameter(3, $location)
            ->setParameter(4, $description)
            ->setParameter(5, $symbol)
            ->execute();

		return ApiHelper::createJsonResponse($response, $share, true);*/
	}
}
