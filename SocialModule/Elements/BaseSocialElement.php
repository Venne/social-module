<?php

/**
 * This file is part of the Venne:CMS (https://github.com/Venne)
 *
 * Copyright (c) 2011, 2012 Josef Kříž (http://www.josef-kriz.cz)
 *
 * For the full copyright and license information, please view
 * the file license.txt that was distributed with this source code.
 */

namespace SocialModule\Elements;

use Venne;
use CmsModule\Content\Elements\BaseElement;

/**
 * @author Josef Kříž <pepakriz@gmail.com>
 */
abstract class BaseSocialElement extends BaseElement
{

	/** @var \Facebook */
	private $facebook;


	protected function getAppId()
	{
		return $this->getEntity()->appId;
	}


	protected function getSecret()
	{
		return $this->getEntity()->secret;
	}


	protected function getCookie()
	{
		return $this->getEntity()->cookie;
	}


	protected function getFacebook()
	{
		if (!$this->facebook) {
			$this->facebook = new \Facebook(array(
				'appId' => $this->getAppId(),
				'secret' => $this->getSecret(),
				'cookie' => $this->getCookie(),
			));
		}
		return $this->facebook;
	}


	protected function api()
	{
		return call_user_func_array(array($this->getFacebook(), 'api'), func_get_args());
	}


	protected function runFql($query)
	{
		return $this->api(array(
			'method' => 'fql.query',
			'query' => $query,
		));
	}
}
