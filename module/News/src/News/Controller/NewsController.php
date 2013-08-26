<?php
namespace News\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use News\Model\Comments;
use News\Model\News;
use News\Form\NewsForm;
use News\Form\CommentsForm;
use News\Form\AdminForm;
use Zend\Authentication\AuthenticationService;

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
            setcookie("spos", -2);
            $_COOKIE['spos'] = -2;
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
        if( !$GLOBALS['isAdmin'] ) return $this->redirect()->toRoute('news');
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
        if( !$GLOBALS['isAdmin'] ) return $this->redirect()->toRoute('news');
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
        if( !$GLOBALS['isAdmin'] ) return $this->redirect()->toRoute('news');
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
        $id = (int) $this->params()->fromRoute('id', 0);
        if( $id<0 ){ $mass['moderate']=true; $id = -$id; }
        if (!$id) {                                 
            return $this->redirect()->toRoute('news');
        }
        
        try {
            $news = $this->getNewsTable()->getNews($id);
        }
        catch (\Exception $ex) {
            return $this->redirect()->toRoute('news');
        }

        $mass['news'] = $news;
        return $mass;
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
            $post = array_merge_recursive(
                $request->getPost()->toArray(),
                $request->getFiles()->toArray()
            );
            $form->setData($post);
            
            if ($form->isValid()) {
                print_r($request->getFiles()->toArray());
                $coms->exchangeArray($form->getData());
                echo $coms->pict;
                if( !$GLOBALS['isAdmin'] ) $coms->status = 0;
                $this->getCommentsTable()->saveComments($coms, 0, $mass['news']->id);

                //echo $this->_helper->url('news', array('action' => 'read', 'id' => $mass['news']->id));
                return $this->redirect()->toRoute('news', array('action' => 'read', 'id' => $mass['news']->id));
            }   
            $coms->id = $id;
        }

        $mass['form'] = $form;
        return $mass;
    }

    public function ceditAction()
    {
        if( !$GLOBALS['isAdmin'] ) return $this->redirect()->toRoute('news');
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
        if( $coms->pict ) $form->get('ispict')->setValue("1");
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
        if( !$GLOBALS['isAdmin'] ) return $this->redirect()->toRoute('news');
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
    
    public function adminAction(){
        $form = new AdminForm();

        $request = $this->getRequest();
        if ($request->isPost()) {
            $form->setData($request->getPost());

            if ($form->isValid()) {
                $auth = new AuthenticationService();
                if ($auth->hasIdentity()) $auth->clearIdentity();
                $GLOBALS['isAdmin'] = false;

                // Set up the authentication adapter
                $authAdapter = new \News\Auth\NewsAdapter($form->get('username')->getValue(), $form->get('password')->getValue());

                // Attempt authentication, saving the result
                $result = $auth->authenticate($authAdapter);

                if (!$result->isValid()) {
                    $mass['failed'] = 1;
                } else {
                    return $this->redirect()->toRoute('news');
                }
            }
        }
        $mass['form'] = $form;
        return $mass;;
   }
   
   public function logoutAction(){
        $auth = new AuthenticationService();
        if ($auth->hasIdentity()) $auth->clearIdentity();
        return $this->redirect()->toRoute('news');
   }
}