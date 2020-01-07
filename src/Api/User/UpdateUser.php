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
				throw new HttpBadRequestException($request, 'USERNAME_TAKEN');

		if(isset($body->email) && $this->container->get('UserService')->fetchUserIdPerEmail($body->email))
			throw new HttpBadRequestException($request, 'EMAIL_TAKEN');

		$query = $this->getDbalQueryBuilder()->update('users');

		$dirty = false;
		if(isset($body->username)) {
			$query = $query->set('username', ':username')->setParameter('username', $body->username, \Doctrine\DBAL\ParameterType::STRING);
			$dirty = true;
		}

		if(isset($body->email)) {
			$emailVerifyToken = $this->container->get('AuthenticationService')->getEmailVerificationToken((int)$this->getTokenPayload()->sub, $body->email);
			$user =  $this->container->get('UserService')->fetchUser((int)$this->getTokenPayload()->sub);

			$this->container->get('SmtpService')->sendMail(
				'noreply',
				[$body->email],
				'Verify your Email',
				$this->container->get('SmtpService')->loadTemplate('email-verification-change.html', [
					'recipient' => $user['username'],
					'verifyLink' => 'https://logigator.com/verify-email/' . $emailVerifyToken
				])
			);
		}

		if(isset($body->password)) {
			$query = $query->set('password', ':password')->setParameter('password', password_hash($body->password, PASSWORD_DEFAULT), \Doctrine\DBAL\ParameterType::STRING);
			$dirty = true;
		}

		if($dirty === true)
			$query->where('pk_id = :pk_id')->setParameter('pk_id', (int)$this->getTokenPayload()->sub, \Doctrine\DBAL\ParameterType::INTEGER)->execute();

		if (isset($body->shortcuts)) {
			foreach ($body->shortcuts as $key => $value) {
				if($this->getDbalQueryBuilder()
					->select('pk_id')
					->from('shortcuts')
					->where('fk_user = :user and name = :shortcut')
					->setParameter('user', (int)$this->getTokenPayload()->sub, \Doctrine\DBAL\ParameterType::INTEGER)
					->setParameter('shortcut', $key, \Doctrine\DBAL\ParameterType::STRING)
					->execute()
					->fetch()) {
					$this->getDbalQueryBuilder()
						->update('shortcuts')
						->set('key_code', ':key')
						->set('shift', ':shift')
						->set('ctrl', ':ctrl')
						->set('alt', ':alt')
						->where('fk_user = :user and name = :shortcut')
						->setParameter('key', $value->key_code, \Doctrine\DBAL\ParameterType::STRING)
						->setParameter('shift', $value->shift, \Doctrine\DBAL\ParameterType::BOOLEAN)
						->setParameter('ctrl', $value->ctrl, \Doctrine\DBAL\ParameterType::BOOLEAN)
						->setParameter('alt', $value->alt, \Doctrine\DBAL\ParameterType::BOOLEAN)
						->setParameter('user', (int)$this->getTokenPayload()->sub, \Doctrine\DBAL\ParameterType::INTEGER)
						->setParameter('shortcut', $key, \Doctrine\DBAL\ParameterType::STRING)
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
						->setParameter('key', $value->key_code, \Doctrine\DBAL\ParameterType::STRING)
						->setParameter('shift', $value->shift, \Doctrine\DBAL\ParameterType::BOOLEAN)
						->setParameter('ctrl', $value->ctrl, \Doctrine\DBAL\ParameterType::BOOLEAN)
						->setParameter('alt', $value->alt, \Doctrine\DBAL\ParameterType::BOOLEAN)
						->setParameter('user', (int)$this->getTokenPayload()->sub, \Doctrine\DBAL\ParameterType::INTEGER)
						->setParameter('shortcut', $key, \Doctrine\DBAL\ParameterType::STRING)
						->execute();
				}
			}
		}

		return ApiHelper::createJsonResponse($response, ['success' => true]);
	}
}
