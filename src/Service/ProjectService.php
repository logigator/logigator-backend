<?php

namespace Logigator\Service;

use Logigator\Api\ApiHelper;
use Ramsey\Uuid\Uuid;

class ProjectService extends BaseService
{
	public function fetchLocation(int $projectId, int $userId)
	{
		return $this->container->get('DbalService')->getQueryBuilder()
			->select('location')
			->from('projects')
			->where('pk_id = ? and fk_user = ?')
			->setParameter(0, $projectId, \Doctrine\DBAL\ParameterType::INTEGER)
			->setParameter(1, $userId, \Doctrine\DBAL\ParameterType::INTEGER)
			->execute()
			->fetch()["location"];
	}

	public function getAllProjectsInfo(int $userId): array
	{
		return $this->container->get('DbalService')->getQueryBuilder()
			->select('pk_id, name, description, last_edited, created_on, location')
			->from('projects')
			->where('fk_user = ? and is_component = false')
			->orderBy('last_edited', 'DESC')
			->setParameter(0, $userId, \Doctrine\DBAL\ParameterType::INTEGER)
			->execute()
			->fetchAll();
	}

	public function getProjectInfo(int $projectId, int $userId = null)
	{
		$query = $this->container->get('DbalService')->getQueryBuilder()
			->select('*')
			->from('projects');

		if ($userId === null)
			$query = $query
				->where('pk_id = ?')
				->setParameter(0, $projectId, \Doctrine\DBAL\ParameterType::INTEGER);
		else
			$query = $query
				->where('pk_id = ? and fk_user = ?')
				->setParameter(0, $projectId, \Doctrine\DBAL\ParameterType::STRING)
				->setParameter(1, $userId, \Doctrine\DBAL\ParameterType::INTEGER);

		return $query->execute()->fetch();
	}

	public function getAllComponentsInfo(int $userId): array
	{
		return $this->container->get('DbalService')->getQueryBuilder()
			->select('pk_id, name, description, symbol, last_edited, created_on, location, num_inputs, num_outputs')
			->from('projects')
			->where('fk_user = ? and is_component = true')
			->orderBy('last_edited', 'DESC')
			->setParameter(0, $userId, \Doctrine\DBAL\ParameterType::INTEGER)
			->execute()
			->fetchAll();
	}

	public function fetchProjectId($location, $userId): int
	{
		return $this->container->get('DbalService')->getQueryBuilder()
			->select('pk_id')
			->from('projects')
			->where('location = ? and fk_user = ?')
			->setParameter(0, $location, \Doctrine\DBAL\ParameterType::STRING)
			->setParameter(1, $userId, \Doctrine\DBAL\ParameterType::INTEGER)
			->execute()
			->fetch()["pk_id"];
	}

	public function fetchShare(string $address, int $userId, $anonymous = false) {
		$share = $this->container->get('DbalService')->getQueryBuilder()
			->select('link.address as "link.address", 
                link.is_public as "link.is_public",
                link.pk_id as "link.pk_id",
                project.pk_id as "project.id",
                project.name as "project.name",
                project.description as "project.description",
                project.symbol as "project.symbol", 
                project.last_edited as "project.last_edited",
                project.created_on as "project.created_on", 
                project.is_component as "project.is_component", 
                project.location as "project.location", 
                user.username as "user.username",
                user.profile_image as "user.profile_image",
                user.pk_id as "user.pk_id"')
			->from('links', 'link')
			->join('link', 'projects', 'project', 'link.fk_project = project.pk_id')
			->join('project', 'users', 'user', 'user.pk_id = project.fk_user')
			->where('link.address = ?')
			->setParameter(0, $address, \Doctrine\DBAL\ParameterType::STRING)
			->execute()
			->fetch();

		if(!$share)
			return false;

		if($share['link.is_public'])
			return $share;

		if($anonymous)
			return false;

		if(!$this->container->get('DbalService')->getQueryBuilder()
			->select('fk_user, fk_link')
			->from('link_permits')
			->where('fk_user = ? and fk_link = ?')
			->setParameter(0, $userId, \Doctrine\DBAL\ParameterType::INTEGER)
			->setParameter(1, $share['link.pk_id'], \Doctrine\DBAL\ParameterType::INTEGER)
			->execute()
			->fetch())
			return false;

		return $share;
	}

	public function setSharePermits($link_id, $users, $invitations = false, $invitor = 'user', $project_name = 'project', $link ='') {
		$warnings = ['not_found' => [], 'duplicates' => []];
		$added = [];
		foreach($users as $u) {
			$userData = $this->container->get('DbalService')->getQueryBuilder()
				->select('*')
				->from('users')
				->where('username = ? or email = ?')
				->setParameter(0, $u, \Doctrine\DBAL\ParameterType::STRING)
				->setParameter(1, $u, \Doctrine\DBAL\ParameterType::STRING)
				->execute()
				->fetch();

			if(!$userData) {
				array_push($warnings['not_found'], $u);
				continue;
			}

			if (in_array($userData['pk_id'], $added)) {
				array_push($warnings['duplicates'], $u);
				continue;
			}
			$added[] = $userData['pk_id'];

			$this->container->get('DbalService')->getQueryBuilder()
				->insert('link_permits')
				->setValue('fk_user', '?')
				->setValue('fk_link', '?')
				->setParameter(0, $userData['pk_id'], \Doctrine\DBAL\ParameterType::INTEGER)
				->setParameter(1, $link_id, \Doctrine\DBAL\ParameterType::INTEGER)
				->execute();

			if($invitations === true) {
				try {
					$this->container->get('SmtpService')->sendMail(
						'noreply', [
						$userData['email']
					],
						'Someone shared his project with you!',
						$this->container->get('SmtpService')->loadTemplate('share-invitation.html', [
							'recipient' => $userData['username'],
							'invitor' => $invitor,
							'project' => $project_name,
							'link' => 'https://editor.logigator.com/share/' . $link
						])
					);
				} catch (\Exception $e) {
					array_push($warnings, 'Failed to send invitation to user "' . $u . '"');
				}
			}
		}
		return $warnings;
	}
}
