<?php
use Zend\Authentication\AuthenticationService;
/**
 * This makes our life easier when dealing with paths. Everything is relative
 * to the application root now.
 */
chdir(dirname(__DIR__));

// Decline static file requests back to the PHP built-in webserver
if (php_sapi_name() === 'cli-server' && is_file(__DIR__ . parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH))) {
    return false;
}

// Setup autoloading
require 'init_autoloader.php';

$auth = new AuthenticationService();
$isAdmin = ($auth->getIdentity()===1);

$id = isset($_GET['p'])?(int)$_GET['p']:0;

if( $id<=0 ){
    header("Content-type: image/png");
    readfile("public/img/nophoto.png");
    exit;
}

$db = require 'config/autoload/global.php';
$db2 = require 'config/autoload/local.php';
$configArray = array_merge($db['db'], $db2['db']);
$adapter = new \Zend\Db\Adapter\Adapter($configArray);
$res = $adapter->query("select pict from comments where id=$id".($isAdmin?"":" and status=1"), $adapter::QUERY_MODE_EXECUTE);
//header("Content-type: image/*");
$cres = $res->current();
if( !($res instanceOf \Zend\Db\ResultSet\ResultSet ) || $cres['pict']==null ){
    header("Content-type: image/png");
    readfile("public/img/nophoto.png");
    exit;
}
//header("Content-type: text/html");
//print_r($db);
//print_r($db2);
//print_r($configArray);
//print_r($res);
//echo $res->;
//echo "ghj";
//print_r($cres);
header("Content-type: image/*");
echo $cres['pict'];

?>