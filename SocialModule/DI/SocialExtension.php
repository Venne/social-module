<?php

/**
 * This file is part of the Venne:CMS (https://github.com/Venne)
 *
 * Copyright (c) 2011, 2012 Josef Kříž (http://www.josef-kriz.cz)
 *
 * For the full copyright and license information, please view
 * the file license.txt that was distributed with this source code.
 */

namespace SocialModule\DI;

use Venne;
use Venne\Config\CompilerExtension;

/**
 * @author Josef Kříž <pepakriz@gmail.com>
 */
class SocialExtension extends CompilerExtension
{

	public $defaults = array(
		'facebook' => array(
			'name' => 'facebook',
			'appId' => NULL,
			'secret' => NULL,
		),
	);


	/**
	 * Processes configuration data. Intended to be overridden by descendant.
	 * @return void
	 */
	public function loadConfiguration()
	{
		parent::loadConfiguration();
		$container = $this->getContainerBuilder();
		$config = $this->getConfig($this->defaults);

		$container->addDefinition($this->prefix('facebookLogin'))
			->setClass('SocialModule\Security\SocialLogins\FacebookLogin', array($config['facebook']['appId'], $config['facebook']['secret']))
			->setAutowired(false)
			->addSetup('injectUserRepository', array('@cms.userRepository'))
			->addSetup('injectCheckConnection', array('@doctrine.checkConnectionFactory'));

	}


	public function beforeCompile()
	{
		parent::beforeCompile();

		$container = $this->getContainerBuilder();
		$config = $this->getConfig($this->defaults);

		$securityManager = $container->getDefinition('cms.securityManager');
		$securityManager->addSetup('addSocialLogin', array($config['facebook']['name'], $this->prefix('facebookLogin')));
	}
}
