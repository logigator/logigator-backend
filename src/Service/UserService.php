<?php


namespace Logigator\Service;

use DI\Annotation\Inject;

class UserService
{

	/**
	 * @Inject
	 * @var DbalService
	 */
	private $dbalService;

	public function createUser($username, $socialMediaKey, $email, $loginType, $password = null, $profile_image = null): int
	{
	    if($password !== null)
	        $password = password_hash($password, PASSWORD_DEFAULT);

		$this->dbalService->getQueryBuilder()
			->insert('users')
			->setValue('username', '?')
			->setValue('password', '?')
			->setValue('email', '?')
			->setValue('login_type', '?')
			->setValue('profile_image', '?')
			->setValue('social_media_key', '?')
			->setParameter(0, $username, \Doctrine\DBAL\ParameterType::STRING)
			->setParameter(1, $password, \Doctrine\DBAL\ParameterType::STRING)
			->setParameter(2, $email, \Doctrine\DBAL\ParameterType::STRING)
			->setParameter(3, $loginType, \Doctrine\DBAL\ParameterType::STRING)
			->setParameter(4, $profile_image, \Doctrine\DBAL\ParameterType::STRING)
			->setParameter(5, $socialMediaKey, \Doctrine\DBAL\ParameterType::STRING)
			->execute();

		return $this->dbalService->getConnection()->lastInsertId();
	}

	public function setEmailVerified($userId, $email) {
		if($this->dbalService->getQueryBuilder()
			->select('pk_id')
			->from('users')
			->where('email = :email and pk_id != :id')
			->setParameter('email', $email, \Doctrine\DBAL\ParameterType::STRING)
			->setParameter('id', $userId, \Doctrine\DBAL\ParameterType::STRING)
			->execute()
			->fetch()['pk_id'] != null)
			return false;

		$login_type = $this->dbalService->getQueryBuilder()
			->select('login_type')
			->from('users')
			->where('pk_id = ?')
			->setParameter(0, $userId, \Doctrine\DBAL\ParameterType::INTEGER)
			->execute()
			->fetch()['login_type'];

		if ($login_type === 'local_not_verified') {
			$this->dbalService->getQueryBuilder()
				->update('users')
				->set('login_type', ':local')
				->where('pk_id = :id')
				->setParameter('local', 'local', \Doctrine\DBAL\ParameterType::STRING)
				->setParameter('id', $userId, \Doctrine\DBAL\ParameterType::INTEGER)
				->execute();
		}

		$this->dbalService->getQueryBuilder()
			->update('users')
			->set('email', ':email')
			->where('pk_id = :id')
			->setParameter('email', $email, \Doctrine\DBAL\ParameterType::STRING)
			->setParameter('id', $userId, \Doctrine\DBAL\ParameterType::INTEGER)
			->execute();

		return true;
	}

	public function fetchUser($id) {
		return $this->dbalService->getQueryBuilder()
			->select('*')
			->from('users')
			->where('pk_id = ?')
			->setParameter(0, $id, \Doctrine\DBAL\ParameterType::INTEGER)
			->execute()
			->fetch();
	}

	public function fetchUserIdPerKey($key, $login_type)
	{
		return $this->dbalService->getQueryBuilder()
			->select('pk_id')
			->from('users')
			->where('social_media_key = ? and login_type = ?')
			->setParameter(0, $key, \Doctrine\DBAL\ParameterType::STRING)
			->setParameter(1, $login_type, \Doctrine\DBAL\ParameterType::STRING)
			->execute()
			->fetch()["pk_id"];
	}

    public function fetchUserIdPerUsername($username)
    {
        return $this->dbalService->getQueryBuilder()
            ->select('pk_id')
            ->from('users')
            ->where('username = ?')
            ->setParameter(0, $username, \Doctrine\DBAL\ParameterType::STRING)
            ->execute()
            ->fetch()["pk_id"];
    }

	public function fetchUserIdPerEmail($email)
	{
		return $this->dbalService->getQueryBuilder()
			->select('pk_id')
			->from('users')
			->where('email = ?')
			->setParameter(0, $email, \Doctrine\DBAL\ParameterType::STRING)
			->execute()
			->fetch()["pk_id"];
	}
}
