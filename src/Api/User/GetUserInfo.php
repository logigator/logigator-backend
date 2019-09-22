<?php


namespace Logigator\Api\User;


use Logigator\Api\ApiHelper;
use Logigator\Api\BaseController;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Slim\Exception\HttpInternalServerErrorException;

class GetUserInfo extends BaseController
{
	public function __invoke(ServerRequestInterface $request, ResponseInterface $response, array $args)
	{
		$data = $this->getDbalQueryBuilder()
			->select('username, email, login_type, profile_image')
			->from('users')
			->where('pk_id = ?')
			->setParameter(0, $this->getTokenPayload()->sub)
			->execute()
			->fetch();

		if(!$data)
			throw new HttpInternalServerErrorException(self::ERROR_RESOURCE_NOT_FOUND);

		if($data['profile_image'] === null)
			$data['profile_image'] = $this->container->get('ConfigService')->getConfig('profile_default_image');

		return ApiHelper::createJsonResponse($response, $data);
	}
}
