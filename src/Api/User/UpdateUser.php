<?php


namespace Logigator\Api\User;


use Logigator\Api\ApiHelper;
use Logigator\Api\BaseController;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Slim\Exception\HttpBadRequestException;

class UpdateUser extends BaseController
{
	public function __invoke(ServerRequestInterface $request, ResponseInterface $response, array $args)
	{
		$body = $request->getParsedBody();

		if(isset($body->username) && $this->container->get('UserService')->fetchUserIdPerUsername($body->username))
				throw new HttpBadRequestException($request, 'Username has already been taken.');

		if(isset($body->email) && $this->container->get('UserService')->fetchUserIdPerEmail($body->email))
			throw new HttpBadRequestException($request, 'Email has already been taken.');

		$query = $this->getDbalQueryBuilder()->update('users');

		$dirty = false;
		if(isset($body->username)) {
			$query = $query->set('username', ':username')->setParameter('username', $body->username);
			$dirty = true;
		}

		if(isset($body->email)) {
			$query = $query->set('email', ':email')->setParameter('email', $body->email);
			$dirty = true;
		}

		if(isset($body->password)) {
			$query = $query->set('password', ':password')->setParameter('password', password_hash($body->password, PASSWORD_DEFAULT));
			$dirty = true;
		}

		if($dirty === true)
			$query->where('pk_id = :pk_id')->setParameter('pk_id', (int)$this->getTokenPayload()->sub)->execute();

		if (isset($body->shortcuts)) {
			foreach ($body->shortcuts as $key => $value) {
				if($this->getDbalQueryBuilder()
					->select('pk_id')
					->from('shortcuts')
					->where('fk_user = :user and name = :shortcut')
					->setParameter('user', (int)$this->getTokenPayload()->sub)
					->setParameter('shortcut', $key)
					->execute()
					->fetch()) {
					$this->getDbalQueryBuilder()
						->update('shortcuts')
						->set('key_code', ':key')
						->set('shift', ':shift')
						->set('ctrl', ':ctrl')
						->set('alt', ':alt')
						->where('fk_user = :user and name = :shortcut')
						->setParameter('key', $value->key_code)
						->setParameter('shift', $value->shift)
						->setParameter('ctrl', $value->ctrl)
						->setParameter('alt', $value->alt)
						->setParameter('user', (int)$this->getTokenPayload()->sub)
						->setParameter('shortcut', $key)
						->execute();
				} else {
					$this->getDbalQueryBuilder()
						->insert('shortcuts')
						->setValue('name', ':shortcut')
						->setValue('key_code', ':key')
						->setValue('shift', ':shift')
						->setValue('ctrl', ':ctrl')
						->setValue('alt', ':alt')
						->setValue('fk_user', ':user')
						->setParameter('key', $value->key_code)
						->setParameter('shift', $value->shift)
						->setParameter('ctrl', $value->ctrl)
						->setParameter('alt', $value->alt)
						->setParameter('user', (int)$this->getTokenPayload()->sub)
						->setParameter('shortcut', $key)
						->execute();
				}
			}
		}

		return ApiHelper::createJsonResponse($response, ['success' => true]);
	}
}
