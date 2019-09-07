<?php


namespace Logigator\Service;


class LinkService extends BaseService
{

	public function fetchLinkData($address, $id){
		return $this->container->get('DbalService')->getQueryBuilder()
			->select('project.pk_id as project_id, user.pk_id as user_id')
			->from('links', 'link')
			->join('link', 'projects', 'project', 'link.fk_project = project.pk_id')
			->join('project', 'users', 'user', 'user.pk_id = project.fk_user')
			->join('link', 'link_permits', 'permit', 'link.pk_id = permit.fk_link')
			->where('link.address = ? and (link.is_public = true or permit.fk_user = ?)')
			->setParameter(0, $address)
			->setParameter(1, $id)
			->execute()
			->fetchAll();
	}

}
