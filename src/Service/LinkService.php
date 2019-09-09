<?php


namespace Logigator\Service;


class LinkService extends BaseService
{

	public function fetchPublicLinkData($address){
		return $this->container->get('DbalService')->getQueryBuilder()
			->select('project.pk_id as project_id, user.pk_id as user_id')
			->from('links', 'link')
			->join('link', 'projects', 'project', 'link.fk_project = project.pk_id')
			->join('project', 'users', 'user', 'user.pk_id = project.fk_user')
			->where('link.address = ? and link.is_public = true')
			->setParameter(0, $address)
			->execute()
			->fetch();
	}

	public function fetchPrivateLinkData($address, $id){
		return $this->container->get('DbalService')->getQueryBuilder()
			->select('project.pk_id as project_id, project.fk_user as user_id')
			->from('link_permits', 'permit')
			->join('permit', 'links', 'link', 'permit.fk_link = link.pk_id')
			->join('link', 'projects' , 'project', 'link.fk_project = project.pk_id')
			->where('permit.fk_user = ? and link.address = ?')
			->setParameter(0, $id)
			->setParameter(1, $address)
			->execute()
			->fetch();
	}



}
