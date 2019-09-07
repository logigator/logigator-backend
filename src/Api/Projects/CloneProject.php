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
		$body = $request->getParsedBody();

		if (!ApiHelper::checkRequiredArgs($body, ['address'])) {
			throw new HttpBadRequestException($request, 'Not all required args were given');
		}
		$id = $this->getTokenPayload()->sub;
		$linkData = $this->container->get('LinkService')->fetchLinkData($body['address'], $id);
		$newProjectId = $this->container->get('ProjectService')->cloneProject($linkData['project_id'], $linkData['user_id'], $id,0);
		//TODO use new project ID to retrieve and return data
		$data = array();
		return ApiHelper::createJsonResponse($response, $data);
	}
}
