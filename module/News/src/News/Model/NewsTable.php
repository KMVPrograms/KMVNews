<?php
namespace News\Model;

use Zend\Db\TableGateway\TableGateway;
use Zend\Db\Sql\Expression;
use Zend\Db\Sql\Sql;
use Zend\Db\Sql\Where;
use Zend\Db\ResultSet\ResultSet;

class NewsTable
{
    protected $tableGateway;

    public function __construct(TableGateway $tableGateway)
    {
        $this->tableGateway = $tableGateway;
    }

    public function fetchAll()
    {
        $like_str = "";
        if( strlen($_COOKIE['search']) ){
            $like_str = '%' . preg_replace(array('#%#', '#\s#'), array('\%', '%'), $_COOKIE['search']) . '%';
        }
        
        $sql = new Sql($this->tableGateway->getAdapter());
        $select = $sql->select("news")->columns(array('cnt' => new Expression('count(*)')));
        if( strlen($like_str) ){
            $wh = new Where();
            $wh->like("title", $like_str)->OR->like("text", $like_str);
            $select->where(array($wh));
        }
//        echo $select->getSqlString();
        $statement = $sql->prepareStatementForSqlObject($select);
        $results = $statement->execute();
        $resultSet = new ResultSet;
        $res = $resultSet->initialize($results)->toArray();
        $GLOBALS['rowcount'] = $res[0]['cnt'];
        $GLOBALS['pagecount'] = ceil($res[0]['cnt']/10);

        if( isset($_GET['spos']) ){
            $ps = $_GET['spos'];
            if( $ps>=$GLOBALS['pagecount'] ) $ps = $GLOBALS['pagecount']-1;
            if( $ps<0 ) $ps=0;
            setcookie("spos", $ps);
            $_COOKIE['spos'] = $ps;
        }

        $sel = $this->tableGateway->getSql()->select();
        $sel->columns(array('id', 'title', 'dt' => new Expression('DATE_FORMAT(dt, "%d.%m.%Y %H:%i:%s")'), 'text'), false);
        if( strlen($like_str) ){
            $sel->where(array($wh));
        }
        //if( $GLOBALS['isAdmin' ) $select->order('status DESC');
        if( isset($_COOKIE['order']) ){
            $ord = (int)$_COOKIE['order'];
            if( $ord!=0 && abs($ord)<3 ){
                $sel->order(((abs($ord)==1)?"title":"news.dt") . (($ord<0)?" DESC":""));
            }
        }
        $sel->offset($_COOKIE['spos']*10);
        $sel->limit(10);
        //echo $sel->getSqlString();
        $resultSet = $this->tableGateway->selectWith($sel);
        //print_r($resultSet);
        //$resultSet[0]->title = 'hello'.$_GLOBALS['rowcount'];
        //$resultSet = $this->tableGateway->select();
        return $resultSet;
    }

    public function getNews($id)
    {
        $id  = (int) $id;
        $sel = $this->tableGateway->getSql()->select();
        $sel->columns(array('id', 'title', 'dt' => new Expression('DATE_FORMAT(dt, "%d.%m.%Y %k:%i:%s")'), 'text'), false)->where(array('id' => $id));
        $rowset = $this->tableGateway->selectWith($sel);
        //$rowset = select(array('id' => $id));
        $row = $rowset->current();
        if (!$row) {
            throw new \Exception("Could not find row $id");
        }
        return $row;
    }

    public function saveNews(News $News)
    {
        $data = array(
            'title' => $News->title,
            'text'  => $News->text,
        );

        $id = (int)$News->id;
        if ($id == 0) {
            $data['dt'] = $News->dt;
            $this->tableGateway->insert($data);
        } else {
            if ($this->getNews($id)) {
                $this->tableGateway->update($data, array('id' => $id));
            } else {
                throw new \Exception('News id does not exist');
            }
        }
    }

    public function deleteNews($id)
    {
        $this->tableGateway->delete(array('id' => $id));
    }
}