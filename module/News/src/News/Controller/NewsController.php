<?php
namespace News\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use News\Model\News;          // <-- Add this import
use News\Form\NewsForm;       // <-- Add this import

class NewsController extends AbstractActionController
{
    protected $NewsTable;
    protected $CommentsTable;

    public function getNewsTable()
    {
        if (!$this->NewsTable) {
            $sm = $this->getServiceLocator();
            $this->NewsTable = $sm->get('News\Model\NewsTable');
        }
        return $this->NewsTable;
    }

    public function getCommentsTable()
    {
        if (!$this->CommentsTable) {
            $sm = $this->getServiceLocator();
            $this->CommentsTable = $sm->get('News\Model\CommentsTable');
        }
        return $this->CommentsTable;
    }

    public function indexAction()
    {
        if( !isset($_COOKIE['spos']) ){
            setcookie("spos", 0);
            $_COOKIE['spos'] = 0;
        }
        if( isset($_GET['order']) ){
            setcookie("order", $_GET['order']);
            $_COOKIE['order'] = $_GET['order'];
        }
        if( isset($_POST['search']) ){
            setcookie("search", $_POST['search']);
            $_COOKIE['search'] = $_POST['search'];
        }
	    //return new ViewModel(array(
        //    'news' => $this->getNewsTable()->fetchAll(),
        //));
        return array(
            'news' => $this->getNewsTable()->fetchAll(),
        );
    }

    public function addAction()
    {
        $form = new NewsForm();
        $form->get('submit')->setValue('Добавить');

        $request = $this->getRequest();
        if ($request->isPost()) {
            $news = new News();
            $form->setInputFilter($news->getInputFilter());
            $form->setData($request->getPost());

            if ($form->isValid()) {
                $news->exchangeArray($form->getData());
                $this->getNewsTable()->saveNews($news);

                return $this->redirect()->toRoute('news');
            }
        }
        return array('form' => $form);
    }

    public function editAction()
    {
        $id = (int) $this->params()->fromRoute('id', 0);
        if (!$id) {
            return $this->redirect()->toRoute('news', array(
                'action' => 'add'
            ));
        }

        try {
            $news = $this->getNewsTable()->getNews($id);
        }
        catch (\Exception $ex) {
            return $this->redirect()->toRoute('news', array(
                'action' => 'index'
            ));
        }

        $form  = new NewsForm();
        $form->bind($news);
        $form->get('submit')->setAttribute('value', 'Редактировать');

        $request = $this->getRequest();
        if ($request->isPost()) {
            $form->setInputFilter($news->getInputFilter());
            $form->setData($request->getPost());

            if ($form->isValid()) {
                $this->getNewsTable()->saveNews($news);

                return $this->redirect()->toRoute('news');
            }
        }

        return array(
            'id' => $id,
            'form' => $form,
        );
    }

    public function deleteAction()
    {
        $id = (int) $this->params()->fromRoute('id', 0);
        if (!$id) {
            return $this->redirect()->toRoute('news');
        }

        $request = $this->getRequest();
        if ($request->isPost()) {
            $del = $request->getPost('del', 'Нет');

            if ($del == 'Да') {
                $id = (int) $request->getPost('id');
                $this->getNewsTable()->deleteNews($id);
            }

            return $this->redirect()->toRoute('news');
        }

        return array(
            'id'    => $id,
            'news' => $this->getNewsTable()->getNews($id)
        );
    }
    
    private function defBehavior(){
        $GLOBALS['isAdmin'] = true;
        $id = (int) $this->params()->fromRoute('id', 0);
        if (!$id) {                                 
            return $this->redirect()->toRoute('news');
        }
        
        try {
            $news = $this->getNewsTable()->getNews($id);
        }
        catch (\Exception $ex) {
            return $this->redirect()->toRoute('news');
        }

        return array(
            "news" => $news,
        );
    }
    
    public function readAction()
    {
        $mass = $this->defBehavior();
        if( !is_array($mass) ) return $mass;
        
        $mass["coms"] = $this->getCommentsTable()->fetchAll($mass['news']->id);
        return $mass;
    }

    public function caddAction()
    {
        $mass = $this->defBehavior();
        if( !is_array($mass) ) return $mass;
//        $mass["coms"] = $this->getCommentsTable()->fetchAll($id);
        
        return $mass;
    }
}