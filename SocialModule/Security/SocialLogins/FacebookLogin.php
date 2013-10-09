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

use CmsModule\Pages\Users\UserEntity;
use CmsModule\Security\Entities\SocialLoginEntity;
use CmsModule\Security\Identity;
use DoctrineModule\Repositories\BaseRepository;
use Nette\Localization\ITranslator;
use Nette\Object;
use Nette\Security\AuthenticationException;

/**
 * @author Josef Kříž <pepakriz@gmail.com>
 */
class FacebookLogin extends Object implements \CmsModule\Security\ISocialLogin
{

	/** @var BaseRepository */
	protected $userRepository;

	/** @var Callback */
	protected $checkConnection;

	/** @var Facebook */
	protected $facebook;

	protected $user;

	/** @var string */
	protected $appId;

	/** @var string */
	protected $secret;

	/** @var array */
	protected $data;

	/** @var boolean */
	protected $_load;

	/** @var string */
	protected $redirectUri;

	/** @var ITranslator */
	private $translator;


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


	/**
	 * @param ITranslator $translator
	 */
	public function injectTranslator(ITranslator $translator)
	{
		$this->translator = $translator;
	}


	/**
	 * @return ITranslator
	 */
	protected function getTranslator()
	{
		return $this->translator;
	}


	public function getType()
	{
		return 'facebook';
	}


	/**
	 * @param mixed $redirectUri
	 */
	public function setRedirectUri($redirectUri)
	{
		$this->redirectUri = $redirectUri;
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
	}


	public function getLoginUrl()
	{
		$this->load();

		$params = array();

		if ($this->redirectUri) {
			$params['redirect_uri'] = $this->redirectUri;
		}

		return $this->facebook->getLoginUrl($params);
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


	public function connectWithUser(UserEntity $userEntity)
	{
		$userEntity->addSocialLogin($this->getSocialLoginEntity());
		$this->userRepository->save($userEntity);
	}


	/**
	 * @return SocialLoginEntity
	 */
	protected function getSocialLoginEntity()
	{
		$this->load();

		$entity = new SocialLoginEntity;
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
				/** @var $user \CmsModule\Pages\Users\UserEntity */
				$user = $this->userRepository->createQueryBuilder('a')
					->join('a.socialLogins', 's')
					->where('s.type = :type AND s.uniqueKey = :key')
					->setParameter('type', $this->getType())
					->setParameter('key', $data['id'])->getQuery()->getSingleResult();
			} catch (\Doctrine\ORM\NoResultException $e) {
			}

			if (!isset($user) || !$user) {
				throw new AuthenticationException($this->translator->translate('User does not exist.'), self::INVALID_CREDENTIAL);
			}

			return new Identity($user->email, $user->getRoles());
		}
	}
}
