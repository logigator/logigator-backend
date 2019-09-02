<?php


namespace Logigator\Service;

class UserService extends BaseService
{
	private const DEFAULT_PROFILE_IMAGE = "";

	public function createUser($username,$socialMediaKey, $email, $loginType, $profile_image = self::DEFAULT_PROFILE_IMAGE, $password = null)
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
	}

	public function fetchUserId($key)
	{
		return $this->container->get('DbalService')->getQueryBuilder()
			->select('pk_id')
			->from('users')
			->where('social_media_key = ?')
			->setParameter(0, $key)
			->execute()
			->fetch()["pk_id"];
	}

}
