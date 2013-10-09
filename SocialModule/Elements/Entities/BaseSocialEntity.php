<?php

/**
 * This file is part of the Venne:CMS (https://github.com/Venne)
 *
 * Copyright (c) 2011, 2012 Josef Kříž (http://www.josef-kriz.cz)
 *
 * For the full copyright and license information, please view
 * the file license.txt that was distributed with this source code.
 */

namespace SocialModule\Elements\Entities;

use CmsModule\Content\Elements\ExtendedElementEntity;
use Doctrine\ORM\Mapping as ORM;
use CmsModule\Content\Entities\ElementEntity;

/**
 * @author Josef Kříž <pepakriz@gmail.com>
 */
abstract class BaseSocialEntity extends ExtendedElementEntity
{

	/**
	 * @var string
	 * @ORM\Column(type="text")
	 */
	protected $appId = '';

	/**
	 * @var string
	 * @ORM\Column(type="text")
	 */
	protected $secret = '';

	/**
	 * @var bool
	 * @ORM\Column(type="boolean")
	 */
	protected $cookie = TRUE;


	/**
	 * @param string $appId
	 */
	public function setAppId($appId)
	{
		$this->appId = $appId;
	}


	/**
	 * @return string
	 */
	public function getAppId()
	{
		return $this->appId;
	}


	/**
	 * @param string $secret
	 */
	public function setSecret($secret)
	{
		$this->secret = $secret;
	}


	/**
	 * @return string
	 */
	public function getSecret()
	{
		return $this->secret;
	}


	/**
	 * @param boolean $cookie
	 */
	public function setCookie($cookie)
	{
		$this->cookie = $cookie;
	}


	/**
	 * @return boolean
	 */
	public function getCookie()
	{
		return $this->cookie;
	}
}
