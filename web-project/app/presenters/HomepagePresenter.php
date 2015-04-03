<?php

namespace App\Presenters;

use Nette;


class HomepagePresenter extends Nette\Application\UI\Presenter
{

    /** @var Nette\Database\Context */
    private $database;

    public function __construct(Nette\Database\Context $database)
    {
        $this->database = $database;
    }


	public function renderDefault()
	{

        $this->template->user = $this->database->table('user')
            ->order('id DESC')
            ->limit(5);
	}

    public  function renderShow( $PostID )
{
    $this -> template-> user = $this -> database-> table ( 'user' ) -> get ( $PostID );
}

}
