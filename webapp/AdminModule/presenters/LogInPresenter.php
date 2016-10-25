<?php
namespace Mirin\AdminModule\Presenters;
use Nette\Application\UI;
use Nette\Security;
use Mirin\AdminModule;

class LogInPresenter extends UI\Presenter
{
	public function actionDefault()
	{
		if ($this->getUser()->isLoggedIn()) {
			$this->setView("isLoggedIn");
			return;
		}
	}

	public function renderDefault()
	{

		$template = $this->getTemplate();
		$template->subTitle = "Přihlášení";

		if ($this->getSession()->isStarted()) $this->getSession()->start();
		$token = uniqid("lgin");
		$this->getSession("login")->loginToken = $token;
		$this["logInForm"]["loginToken"]->setValue($token);
	}

	public function renderIsLoggedIn()
	{
		$this->getTemplate()->subTitle = "Již přihlášen";
	}

	public function renderLogOut()
	{
		$this->getTemplate()->subTitle = "Odhlášení";
		$this->getUser()->logout();
	}

	/**
	 * Process the login form and try to log particular use in.
	 * @param UI\Form $form
	 */
	public function handleLogInForm(UI\Form $form)
	{
		$token = $form["loginToken"]->getValue();
		if ($token != $this->getSession("login")->loginToken) {
			$form->addError("token verification failed, maybe session hijacking");
			return;
		}

		try {
			$this->getUser()->login($form["username"]->getValue(), $token, $form["passHashed"]->getValue());
		} catch (Security\AuthenticationException $exception) {
			$form->addError($exception->getMessage());
			return;
		}

		$this->redirect("Articles:");
	}

	/**
	 * @return UI\Form
	 */
	protected function createComponentLogInForm()
	{
		$form = new UI\Form();
		$form->addText("username", "Uživatelské jméno:")
			->setRequired("Please enter your username.");
		$form->addPassword("passOrig", "Heslo:")
			->setRequired("Vyplň heslo");
		$form->addHidden("passHashed");
		$form->addHidden("loginToken");
		$form->addSubmit("logIn", "Přihlásit");

		$form->onSuccess[] = [$this, "handleLogInForm"];
		return $form;
	}
}