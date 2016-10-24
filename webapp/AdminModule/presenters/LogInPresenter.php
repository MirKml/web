<?php
namespace Mirin\AdminModule\Presenters;
use Nette\Application\UI;
use Mirin\AdminModule;

class LogInPresenter extends UI\Presenter
{
	public function renderDefault()
	{
		$template = $this->getTemplate();
		$template->cssFile = "signin.css";
		$template->subTitle = "Přihlášení";

		if ($this->getSession()->isStarted()) $this->getSession()->start();
		$token = uniqid("lgin");
		$this->getSession("login")->loginToken = $token;
		$this["logInForm"]["loginToken"]->setValue($token);
	}

	public function handleLogInForm(UI\Form $form)
	{
		$token = $form["loginToken"]->getValue();
		dump($token == $this->getSession("login")->loginToken);
		dump(hash_hmac("md5", "test", "mirin.cz" . $token));
		dump($form["passHashed"]->getValue());
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