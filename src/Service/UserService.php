<?php


namespace Logigator\Service;

class UserService extends BaseService
{
	public function createUser($username, $socialMediaKey, $email, $loginType, $password = null, $profile_image = '_default'): int
	{
		$this->container->get('DbalService')->getQueryBuilder()
			->insert('users')
			->setValue('username', '?')
			->setValue('password', '?')
			->setValue('email', '?')
			->setValue('login_type', '?')
			->setValue('profile_image', '?')
			->setValue('social_media_key', '?')
			->setParameter(0, $username)
			->setParameter(1, $password)
			->setParameter(2, $email)
			->setParameter(3, $loginType)
			->setParameter(4, $profile_image)
			->setParameter(5, $socialMediaKey)
			->execute();

		return $this->container->get('DbalService')->getConnection()->lastInsertId();
	}

	public function fetchUserIdPerKey($key)
	{
		return $this->container->get('DbalService')->getQueryBuilder()
			->select('pk_id')
			->from('users')
			->where('social_media_key = ?')
			->setParameter(0, $key)
			->execute()
			->fetch()["pk_id"];
	}

    public function fetchUserIdPerUsername($username)
    {
        return $this->container->get('DbalService')->getQueryBuilder()
            ->select('pk_id')
            ->from('users')
            ->where('username = ?')
            ->setParameter(0, $username)
            ->execute()
            ->fetch()["pk_id"];
    }

	public function fetchUserIdPerEmail($email)
	{
		return $this->container->get('DbalService')->getQueryBuilder()
			->select('pk_id')
			->from('users')
			->where('email = ?')
			->setParameter(0, $email)
			->execute()
			->fetch()["pk_id"];
	}

	//TODO: implement secure password verification
	public function verifyPassword($email,$password)
	{
		return $this->container->get('DbalService')->getQueryBuilder()
			->select('password')
			->from('users')
			->where('email = ?')
			->setParameter(0, $email)
			->execute()
			->fetch()["password"]==$password;
	}

}
