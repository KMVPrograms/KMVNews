<?php
namespace News\Auth;

use Zend\Authentication\Adapter\AdapterInterface;
use Zend\Authentication\Adapter\AbstractAdapter;
use Zend\Authentication\Result;

class NewsAdapter extends AbstractAdapter implements AdapterInterface
{
    /**
     * Sets username and password for authentication
     *
     * @return void
     */
    public function __construct($username, $password)
    {
        $this->setIdentity($username)->setCredential($password);
    }

    /**
     * Performs an authentication attempt
     *
     * @return \Zend\Authentication\Result
     * @throws \Zend\Authentication\Adapter\Exception\ExceptionInterface
     *               If authentication cannot be performed
     */
    public function authenticate()
    {
        $admin = require('config\autoload\admin.local.php');
        if( $this->getIdentity()===$admin['username'] && $this->getCredential()===$admin['password'] ){
            return new Result(Result::SUCCESS, 1);
        }else{
            return new Result(Result::FAILURE, null);
        }
    }
}