<?php

namespace App\Presenters;

use App\Model,
	Nette,
    Nette\Utils\FileSystem,
	Nette\Application\UI\Form,
    Nette\Utils\Strings,
    Nette\Directory;


class DashboardPresenter extends Nette\Application\UI\Presenter
{
	/** @var Model\AlbumRepository */
	private $albums;


	public function __construct(Model\AlbumRepository $albums)
	{
		$this->albums = $albums;
	}


	protected function startup()
	{
		parent::startup();

		if (!$this->getUser()->isLoggedIn()) {
			if ($this->getUser()->logoutReason === Nette\Security\IUserStorage::INACTIVITY) {
				$this->flashMessage('You have been signed out due to inactivity. Please sign in again.');
			}
			$this->redirect('Home:in', array('backlink' => $this->storeRequest()));
		}
	}


	/********************* view default *********************/


	public function renderDefault()

	{
        //$storage = new FileStorage('temp');
        //$cache = new Cache($storage, 'namespace');

        //disable cache
       // $cache = \Nette\Environment::getCache();
       // $cache->clean(array($cache::ALL => TRUE));

       // $cache = \Nette\Environment :: getCache ( 'App' );
    // $cache -> clean ( array (\Nette\Caching\Cache :: ALL => true ));
        $this->template->albums = $this->albums->findAllByUser( $this->user->id)->order('name');
	}


	/********************* views add & edit *********************/


	public function renderAdd()
	{
		$this['albumForm']['save']->caption = 'Add';
	}


	public function renderEdit($id = 0)
	{
		$form = $this['albumForm'];
		if (!$form->isSubmitted()) {
			$album = $this->albums->findById($id);
			if (!$album) {
				$this->error('Record not found');
			}
			$form->setDefaults($album);
		}
	}


	/********************* view delete *********************/


	public function renderDelete($id = 0)
	{
		$this->template->album = $this->albums->findById($id);
		if (!$this->template->album) {
			$this->error('Record not found');
		}
	}


	/********************* component factories *********************/


	/**
	 * Edit form factory.
	 * @return Form
	 */
	protected function createComponentAlbumForm()
	{
		$form = new Form;
		$form->addText('name', 'Title:')
			->setRequired('Please enter a title.');

        $form -> addUpload ( "file" , "Upload Image" );

		$form->addSubmit('save', 'Save')
			->setAttribute('class', 'default')
			->onClick[] = array($this, 'albumFormSucceeded');

		$form->addSubmit('cancel', 'Cancel')
			->setValidationScope(array())
			->onClick[] = array($this, 'formCancelled');

		$form->addProtection();
		return $form;
	}


	public function albumFormSucceeded($button)
	{
		$values = $button->getForm()->getValues();
		$id = (int) $this->getParameter('id');
		if ($id) {

            //For Updating Data
            $file= $values['file'];
            if ( $file -> isImage ()) {
                $ff= $this->albums->findById($this->getParameter('id'))->img_link;

                FileSystem::delete("uploads/".$ff);

                $file_name=Strings::random('10','0-9a-z').'-'.$file->name;
                $file -> move ('uploads/'.$file_name);
                $update=array(

                    "img_link"=> $file_name
                );
                $this->albums->findById($id)->update($update);
            }
            $han=array(

                "name" => $values['name'],
                "create_date" => date("y-m-d h:i:s"),
                "user_id" => $this->user->id

            );


			$this->albums->findById($id)->update($han);
			$this->flashMessage('The gallery has been updated.');
		}
        else {
            //For Inserting Data
            $file= $values['file'];
            $han=array(

                "name" => $values['name'],
                "create_date" => date("y-m-d h:i:s"),
                "user_id" => $this->user->id

            );

            $row=$this->albums->insert($han);


            if ( $file -> isImage ()) {
                $file_name=Strings::random('10','0-9a-z').'-'.$file->name;
                $file -> move ('uploads/'.$file_name);
                $update=array(

                    "img_link"=> $file_name
                );
                $this->albums->findById($row->id)->update($update);
            }





			//
			$this->flashMessage('The Image has been added.');
		}
		$this->redirect('default');
	}


	/**
	 * Delete form factory.
	 * @return Form
	 */
	protected function createComponentDeleteForm()
	{
		$form = new Form;
		$form->addSubmit('cancel', 'Cancel')
			->onClick[] = array($this, 'formCancelled');

		$form->addSubmit('delete', 'Delete')
			->setAttribute('class', 'default')
			->onClick[] = array($this, 'deleteFormSucceeded');

		$form->addProtection();
		return $form;
	}


	public function deleteFormSucceeded()
	{

$ff= $this->albums->findById($this->getParameter('id'))->img_link;

FileSystem::delete("uploads/".$ff);

        $this->albums->findById($this->getParameter('id'))->delete();



		$this->flashMessage('Image has been deleted.');
		$this->redirect('default');
	}


	public function formCancelled()
	{
		$this->redirect('default');
	}

}
