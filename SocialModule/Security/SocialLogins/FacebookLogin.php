<?php

/**
 * This file is part of the Venne:CMS (https://github.com/Venne)
 *
 * Copyright (c) 2011, 2012 Josef Kříž (http://www.josef-kriz.cz)
 *
 * For the full copyright and license information, please view
 * the file license.txt that was distributed with this source code.
 */

namespace SocialModule\Security\SocialLogins;

use Venne;
use Nette\Object;
use DoctrineModule\Repositories\BaseRepository;

/**
 * @author Josef Kříž <pepakriz@gmail.com>
 */
class FacebookLogin extends Object implements \CmsModule\Security\ISocialLogin
{

	/** @var BaseRepository */
	protected $userRepository;

	protected $checkConnection;

	/** @var Facebook */
	protected $facebook;

	protected $user;

	/** @var string */
	protected $appId;

	/** @var string */
	protected $secret;

	protected $loginUrl;

	protected $logoutUrl;

	protected $data;

	protected $_load;


	/**
	 * @param $appId
	 * @param $secret
	 */
	public function __construct($appId, $secret)
	{
		$this->appId = $appId;
		$this->secret = $secret;
	}


	/**
	 * @param BaseRepository $userRepository
	 */
	public function injectUserRepository(BaseRepository $userRepository)
	{
		$this->userRepository = $userRepository;
	}


	public function injectCheckConnection($checkConnection)
	{
		$this->checkConnection = $checkConnection;
	}


	public function getType()
	{
		return 'facebook';
	}


	protected function load()
	{
		if ($this->_load) {
			return;
		}
		$this->_load = true;

		$this->facebook = new \Facebook(array(
			'appId' => $this->appId,
			'secret' => $this->secret,
		));

		$this->user = $this->facebook->getUser();

		if ($this->user) {
			try {
				$this->data = $this->facebook->api('/me');
			} catch (\FacebookApiException $e) {
				$this->user = null;
			}
		}

		if ($this->user) {
			$this->logoutUrl = $this->facebook->getLogoutUrl();
		} else {
			$this->loginUrl = $this->facebook->getLoginUrl(array(
				'scope' => 'username',
				'redirect_uri' => 'https://www.myapp.com/post_login_page'
			));
		}
	}


	public function getLoginUrl()
	{
		$this->load();

		return $this->loginUrl;
	}


	public function getData()
	{
		$this->load();

		return $this->data;
	}


	public function getKey()
	{
		$this->load();

		return $this->data['id'];
	}


	public function getEmail()
	{
		$this->load();

		return $this->data['username'] . '@facebook.com';
	}


	public function connectWithUser(\CmsModule\Security\Entities\UserEntity $userEntity)
	{
		$userEntity->addSocialLogin($this->getSocialLoginEntity());
		$this->userRepository->save($userEntity);
	}


	/**
	 * @return \CmsModule\Security\Entities\SocialLoginEntity
	 */
	protected function getSocialLoginEntity()
	{
		$this->load();

		$entity = new \CmsModule\Security\Entities\SocialLoginEntity();
		$entity->setData($this->getData());
		$entity->setUniqueKey($this->getKey());
		$entity->setType($this->getType());

		return $entity;
	}


	public function authenticate(array $credentials)
	{
		if ($this->checkConnection->invoke()) {

			$data = $this->getData();

			try {
				/** @var $user \CmsModule\Security\Entities\UserEntity */
				$user = $this->userRepository->createQueryBuilder('a')
					->join('a.socialLogins', 's')
					->where('s.type = :type AND s.uniqueKey = :key')
					->setParameter('type', $this->getType())
					->setParameter('key', $data['id'])->getQuery()->getSingleResult();
			} catch (\Doctrine\ORM\NoResultException $e) {
			}

			if ($user) {
				return new \Nette\Security\Identity($user->getEmail(), $user->getRoles());
			}
		}
	}
}
