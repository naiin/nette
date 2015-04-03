<?php

namespace App\Presenters;

use Nette,
	Nette\Application\UI;


class SignInPresenter extends Nette\Application\UI\Presenter
{
	/** @persistent */
	public $backlink = '';


	/**
	 * Home-in form factory.
	 * @return Nette\Application\UI\Form
	 */
	protected function createComponentSignInForm()
	{
		$form = new UI\Form;
		$form->addText('email', 'Email:')
			->setRequired('Please enter your email.');

		$form->addPassword('password', 'Password:')
			->setRequired('Please enter your password.');

		$form->addSubmit('send', 'Home in');

		$form->onSuccess[] = array($this, 'signInFormSucceeded');
		return $form;
	}


	public function signInFormSucceeded($form, $values)
	{
		try {
			$this->getUser()->login($values->email, $values->password);

		} catch (Nette\Security\AuthenticationException $e) {
			$form->addError($e->getMessage());
			return;
		}

		$this->restoreRequest($this->backlink);
		$this->redirect('Dashboard:');
	}


    public function renderDefault()
    {

        $this->template->user = $this->database->table('user')
            ->order('id DESC')
            ->limit(5);
    }


	public function actionOut()
	{
		$this->getUser()->logout();
		$this->flashMessage('You have been signed out.');
		$this->redirect('in');
	}

}
