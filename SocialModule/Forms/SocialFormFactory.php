<?php

/**
 * This file is part of the Venne:CMS (https://github.com/Venne)
 *
 * Copyright (c) 2011, 2012 Josef Kříž (http://www.josef-kriz.cz)
 *
 * For the full copyright and license information, please view
 * the file license.txt that was distributed with this source code.
 */

namespace SocialModule\Forms;

use Venne;
use Venne\Forms\FormFactory;
use Venne\Forms\Form;
use FormsModule\Mappers\ConfigMapper;

/**
 * @author Josef Kříž <pepakriz@gmail.com>
 */
class SocialFormFactory extends FormFactory
{

	/** @var ConfigMapper */
	protected $mapper;


	/**
	 * @param ConfigMapper $mapper
	 */
	public function __construct(ConfigMapper $mapper)
	{
		$this->mapper = $mapper;
	}


	protected function getMapper()
	{
		$mapper = clone $this->mapper;
		$mapper->setRoot('social');
		return $mapper;
	}


	/**
	 * @param Form $form
	 */
	protected function configure(Form $form)
	{
		$facebook = $form->addContainer('facebook');
		$facebook->setCurrentGroup($form->addGroup('Facebook'));
		$facebook->addText('name', 'Name')->addRule($form::FILLED);
		$facebook->addText('appId', 'App ID');
		$facebook->addText('secret', 'Secret');

		$form->addSaveButton('Save');
	}


	public function handleLoad(Form $form)
	{
		if(!$form['facebook']['name']->value) {
			$form['facebook']['name']->value = 'facebook';
		}
	}
}
