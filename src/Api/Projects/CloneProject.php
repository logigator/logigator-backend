<?php
/**
 * Created by PhpStorm.
 * User: Stefan
 * Date: 31.08.2019
 * Time: 10:41
 */

namespace Logigator\Api\Projects;


use Logigator\Api\ApiHelper;
use Logigator\Api\BaseController;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;
use Slim\Exception\HttpBadRequestException;

class CloneProject extends BaseController
{
	public function __invoke(ServerRequestInterface $request, ResponseInterface $response, array $args)
	{
		$id = $this->getTokenPayload()->sub;
		$linkData = $this->container->get('LinkService')->fetchPublicLinkData($args['address']);

		if (!$linkData)
			$linkData = $this->container->get('LinkService')->fetchPrivateLinkData($args['address'], $id);

		if (!$linkData)
			throw new HttpBadRequestException($request, "Share not found.");

		$newProjectId = $this->container->get('ProjectService')->cloneProject($linkData['project_id'], $linkData['user_id'], $id, 0);
		if($newProjectId < 0)
		    throw new \Exception();

		return ApiHelper::createJsonResponse($response, ['pk_id' => $newProjectId]);
	}
}
