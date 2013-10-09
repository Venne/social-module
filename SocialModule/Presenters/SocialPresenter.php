<?php

/**
 * This file is part of the Venne:CMS (https://github.com/Venne)
 *
 * Copyright (c) 2011, 2012 Josef Kříž (http://www.josef-kriz.cz)
 *
 * For the full copyright and license information, please view
 * the file license.txt that was distributed with this source code.
 */

namespace SocialModule\Presenters;

use SocialModule\Forms\SocialFormFactory;

/**
 * @author Josef Kříž <pepakriz@gmail.com>
 *
 * @secured
 */
class SocialPresenter extends \CmsModule\Administration\Presenters\BasePresenter
{

	/** @var SocialFormFactory */
	protected $socialFormFactory;


	/**
	 * @param SocialFormFactory $socialFormFactory
	 */
	public function injectSocialFormFactory(SocialFormFactory $socialFormFactory)
	{
		$this->socialFormFactory = $socialFormFactory;
	}


	/**
	 * @secured(privilege="show")
	 */
	public function actionDefault()
	{
	}


	protected function createComponentForm()
	{
		$form = $this->socialFormFactory->invoke();
		$form->onSuccess[] = $this->formSuccess;
		return $form;
	}


	public function formSuccess()
	{
		$this->flashMessage($this->translator->translate('Form has been saved'), 'success');

		if (!$this->isAjax()) {
			$this->redirect('this');
		}

		$this->invalidateControl('content');
		$this->payload->url = $this->link('this');
	}
}
