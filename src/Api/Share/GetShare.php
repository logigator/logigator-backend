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
		if($this->isUserAuthenticated())
            $share = $this->container->get("ProjectService")->fetchShare($args['address'], $this->getTokenPayload()->sub);
		else
			$share = $this->container->get("ProjectService")->fetchShare($args['address'], 0, true);

        if(!$share)
            throw new HttpBadRequestException($request, self::ERROR_RESOURCE_NOT_FOUND);

		if(file_exists(ApiHelper::getProjectPath($this->container, $share['project.location'])))
			$share['data'] = json_decode(file_get_contents(ApiHelper::getProjectPath($this->container, $share['project.location'])));
		else
			$share['data'] = [];

        return ApiHelper::createJsonResponse($response, $share, true);
    }
}
