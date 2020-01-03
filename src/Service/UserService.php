<?php


namespace Logigator\Service;

class UserService extends BaseService
{
	public function createUser($username, $socialMediaKey, $email, $loginType, $password = null, $profile_image = null): int
	{
	    if($password !== null)
	        $password = password_hash($password, PASSWORD_DEFAULT);

		$this->container->get('DbalService')->getQueryBuilder()
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

		return $this->container->get('DbalService')->getConnection()->lastInsertId();
	}

	public function setEmailVerified($userId) {
		$this->container->get('DbalService')->getQueryBuilder()
			->update('users')
			->set('login_type', ':local')
			->where('pk_id = :id')
			->setParameter('local', 'local', \Doctrine\DBAL\ParameterType::STRING)
			->setParameter('id', $userId, \Doctrine\DBAL\ParameterType::INTEGER)
			->execute();
	}

	public function fetchUser($id) {
		return $this->container->get('DbalService')->getQueryBuilder()
			->select('*')
			->from('users')
			->where('pk_id = ?')
			->setParameter(0, $id, \Doctrine\DBAL\ParameterType::INTEGER)
			->execute()
			->fetch();
	}

	public function fetchUserIdPerKey($key, $login_type)
	{
		return $this->container->get('DbalService')->getQueryBuilder()
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
        return $this->container->get('DbalService')->getQueryBuilder()
            ->select('pk_id')
            ->from('users')
            ->where('username = ?')
            ->setParameter(0, $username, \Doctrine\DBAL\ParameterType::STRING)
            ->execute()
            ->fetch()["pk_id"];
    }

	public function fetchUserIdPerEmail($email)
	{
		return $this->container->get('DbalService')->getQueryBuilder()
			->select('pk_id')
			->from('users')
			->where('email = ?')
			->setParameter(0, $email, \Doctrine\DBAL\ParameterType::STRING)
			->execute()
			->fetch()["pk_id"];
	}
}
