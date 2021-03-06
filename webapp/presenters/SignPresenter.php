<?php

namespace App\Presenters;

use Nette;
use App\Forms;


class SignPresenter extends Nette\Application\UI\Presenter
{
	/** @var Forms\SignInFormFactory @inject */
	#public $signInFactory;

	/** @var Forms\SignUpFormFactory @inject */
	#public $signUpFactory;


	/**
	 * Sign-in form factory.
	 * @return Nette\Application\UI\Form
	 */
	protected function createComponentSignInForm()
	{
		return $this->signInFactory->create(function () {
			$this->redirect('Index:');
		});
	}


	/**
	 * Sign-up form factory.
	 * @return Nette\Application\UI\Form
	 */
	protected function createComponentSignUpForm()
	{
		return $this->signUpFactory->create(function () {
			$this->redirect('Index:');
		});
	}



}
