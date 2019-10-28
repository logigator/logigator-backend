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
		$user = $this->getDbalQueryBuilder()
			->select('username, email, login_type, profile_image')
			->from('users')
			->where('users.pk_id = ?')
			->setParameter(0, $this->getTokenPayload()->sub)
			->execute()
			->fetch();

		$shortcuts = $this->getDbalQueryBuilder()
			->select('name, key_code, shift, ctrl, alt')
			->from('shortcuts')
			->where('fk_user = ?')
			->setParameter(0, $this->getTokenPayload()->sub)
			->execute()
			->fetchAll();

		if(!$user)
			throw new HttpInternalServerErrorException($request, self::ERROR_RESOURCE_NOT_FOUND);

		if($user['profile_image'] === null)
			$user['profile_image'] = $this->container->get('ConfigService')->getConfig('profile_default_image');

		for($i = 0; $i < count($shortcuts); $i++) {
			$shortcuts[$i]['shift'] = !!$shortcuts[$i]['shift'];
			$shortcuts[$i]['ctrl'] = !!$shortcuts[$i]['ctrl'];
			$shortcuts[$i]['alt'] = !!$shortcuts[$i]['alt'];
		}

		return ApiHelper::createJsonResponse($response, ['user' => $user, 'shortcuts' => $shortcuts]);
	}
}
