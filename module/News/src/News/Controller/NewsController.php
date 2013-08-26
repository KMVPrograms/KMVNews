<?php
namespace News\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use News\Model\Comments;          // <-- Add this import
use News\Model\News;          // <-- Add this import
use News\Form\NewsForm;       // <-- Add this import
use News\Form\CommentsForm;       // <-- Add this import

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
        
        $form = new CommentsForm();
        $form->get('submit')->setValue('Добавить');

        $request = $this->getRequest();
        if ($request->isPost()) {
            $coms = new Comments();
            $form->setInputFilter($coms->getInputFilter());
            $form->setData($request->getPost());

            if ($form->isValid()) {
                $coms->exchangeArray($form->getData());
                if( !$GLOBALS['isAdmin'] ) $coms->status = 0;
                $this->getCommentsTable()->saveComments($coms, 0, $mass['news']->id);

                return $this->redirect()->toRoute('news', array('action' => 'read', 'id' => $mass['news']->id));
            }   
        }

        $mass['form'] = $form;
        return $mass;
    }

    public function ceditAction()
    {
        $mass = $this->defBehavior();
        if( !is_array($mass) ) return $mass;

        $id = (int) $this->params()->fromRoute('cid', 0);
        $nid = (int) $this->params()->fromRoute('id', 0);
        if (!$id) return $this->redirect()->toRoute('news', array('action' => 'cadd', 'id' => $nid));

        try {
            $coms = $this->getCommentsTable()->getComments($id, $nid);
        }
        catch (\Exception $ex) {
            return $this->redirect()->toRoute('news', array('action' => 'read', 'id' => $nid));
        }

        $form = new CommentsForm();
        if( $coms->pict ){
            $fl = fopen('public\img\pict0.png', 'w+b');
            fwrite($fl, $coms->pict);
            fclose($fl);
            $form->get('ispict')->setValue("1");
                //echo "test";
                //header("Content-type: image/png", false); 
                //print $coms->pict;
                //readfile()
                //exit;
        }
        $coms->pict = null;

        $form->bind($coms);
        $form->get('submit')->setAttribute('value', 'Редактировать');
        $form->setInputFilter($coms->getInputFilter());

        $request = $this->getRequest();
        if ($request->isPost()) {
            $post = array_merge_recursive(
                $request->getPost()->toArray(),
                $request->getFiles()->toArray()
            );
            $form->setData($post);
            
            if ($form->isValid()) {
                //print_r($request->getFiles()->toArray());
                //echo $coms->pict;
                $this->getCommentsTable()->saveComments($coms, $id, $nid, $request->getPost('ispict'));
                
                return $this->redirect()->toRoute('news', array('action' => 'read', 'id' => $nid));
            }
            $coms->id = $id;
        }
    
        $mass['form'] = $form;
        $mass['coms'] = $coms;
        return $mass;
    }
    
    public function cdeleteAction()
    {
        $mass = $this->defBehavior();
        if( !is_array($mass) ) return $mass;

        $id = (int) $this->params()->fromRoute('cid', 0);
        $nid = (int) $this->params()->fromRoute('id', 0);
        if (!$id) return $this->redirect()->toRoute('news', array('action' => 'read', 'id' => $nid));

        $request = $this->getRequest();
        if ($request->isPost()) {
            $del = $request->getPost('del', 'Нет');

            if ($del == 'Да') {
                $this->getCommentsTable()->deleteComments($id, $nid);
            }

            return $this->redirect()->toRoute('news', array('action' => 'read', 'id' => $nid));
        }
        
        try {
            $coms = $this->getCommentsTable()->getComments($id, $nid);
        }
        catch (\Exception $ex) {
            return $this->redirect()->toRoute('news', array('action' => 'read', 'id' => $nid));
        }

        $mass['coms'] = $coms;
        return $mass;
    }
}