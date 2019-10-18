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
			$share = $this->container->get("ProjectService")->fetchShare($args['address'], null, true);

        if(!$share)
            throw new HttpBadRequestException($request, self::ERROR_RESOURCE_NOT_FOUND);

        return ApiHelper::createJsonResponse($response, $share, true);
    }
}
