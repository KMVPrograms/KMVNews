<?php
namespace News;

use News\Model\News;
use News\Model\NewsTable;
use News\Model\Comments;
use News\Model\CommentsTable;
use Zend\Db\ResultSet\ResultSet;
use Zend\Db\TableGateway\TableGateway;

class Module
{
    public function getAutoloaderConfig()
    {
        return array(
            'Zend\Loader\ClassMapAutoloader' => array(
                __DIR__ . '/autoload_classmap.php',
            ),
            'Zend\Loader\StandardAutoloader' => array(
                'namespaces' => array(
                    __NAMESPACE__ => __DIR__ . '/src/' . __NAMESPACE__,
                ),
            ),
        );
    }

    public function getConfig()
    {
        return include __DIR__ . '/config/module.config.php';
    }

    public function getServiceConfig()
    {
        return array(
            'factories' => array(
                'News\Model\NewsTable' =>  function($sm) {
                    $tableGateway = $sm->get('NewsTableGateway');
                    $table = new NewsTable($tableGateway);
                    return $table;
                },
                'NewsTableGateway' => function ($sm) {
                    $dbAdapter = $sm->get('Zend\Db\Adapter\Adapter');
                    $resultSetPrototype = new ResultSet();
                    $resultSetPrototype->setArrayObjectPrototype(new News());
                    return new TableGateway('news', $dbAdapter, null, $resultSetPrototype);
                },
                'News\Model\CommentsTable' =>  function($sm) {
                    $tableGateway = $sm->get('CommentsTableGateway');
                    $table = new CommentsTable($tableGateway);
                    return $table;
                },
                'CommentsTableGateway' => function ($sm) {
                    $dbAdapter = $sm->get('Zend\Db\Adapter\Adapter');
                    $resultSetPrototype = new ResultSet();
                    $resultSetPrototype->setArrayObjectPrototype(new Comments());
                    return new TableGateway('comments', $dbAdapter, null, $resultSetPrototype);
                },
            ),
        );
    }
}