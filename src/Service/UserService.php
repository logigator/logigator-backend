<?php


namespace Logigator\Service;

class UserService extends BaseService
{
	public function createUser($username, $socialMediaKey, $email, $loginType, $password = null, $profile_image = '_default'): int
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
			->setParameter(0, $username)
			->setParameter(1, $password)
			->setParameter(2, $email)
			->setParameter(3, $loginType)
			->setParameter(4, $profile_image)
			->setParameter(5, $socialMediaKey)
			->execute();

		return $this->container->get('DbalService')->getConnection()->lastInsertId();
	}

	public function fetchUser($id) {
		return $this->container->get('DbalService')->getQueryBuilder()
			->select('*')
			->from('users')
			->where('pk_id = ?')
			->setParameter(0, $id)
			->execute()
			->fetch();
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

	public function verifyPassword($pk_id, $password): bool
	{
		$hash = $this->container->get('DbalService')->getQueryBuilder()
			->select('password')
			->from('users')
			->where('pk_id = ?')
			->setParameter(0, $pk_id)
			->execute()
			->fetch()["password"];

		if(!$hash)
		    return false;

		return password_verify($password, $hash);
	}

}
