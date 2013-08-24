<?php
namespace News\Model;

use Zend\Db\TableGateway\TableGateway;
use Zend\Db\Sql\Expression;
use Zend\Db\Sql\Sql;
use Zend\Db\Sql\Where;
use Zend\Db\ResultSet\ResultSet;

class CommentsTable
{
    protected $tableGateway;

    public function __construct(TableGateway $tableGateway)
    {
        $this->tableGateway = $tableGateway;
    }

    public function fetchAll($nid)
    {
        $sel = $this->tableGateway->getSql()->select();
        $sel->columns(array('id', 'dt' => new Expression('DATE_FORMAT(dt, "%d.%m.%Y %H:%i:%s")'), 'name', 'email', 'text', 'pict', 'nid', 'status'), false);
        if( !$GLOBALS['isAdmin'] ) $sel->where(array('status' => 0));
        //echo $sel->getSqlString();
        $resultSet = $this->tableGateway->selectWith($sel);
        return $resultSet;
    }

    public function getComments($id)
    {
        $id  = (int) $id;
        $rowset = $this->tableGateway->select(array('id' => $id));
        $row = $rowset->current();
        if (!$row) {
            throw new \Exception("Could not find row $id");
        }
        return $row;
    }

    public function saveComments(Comments $Comments)
    {
        $data = array(
            'dt' => $Comments->dt,
            'name'  => $Comments->name,
            'email'  => $Comments->email,
            'text'  => $Comments->text,
            'pict'  => $Comments->pict,
        );

        $id = (int)$Comments->id;
        if ($id == 0) {
            $this->tableGateway->insert($data);
        } else {
            if ($this->getComments($id)) {
                $this->tableGateway->update($data, array('id' => $id));
            } else {
                throw new \Exception('Comments id does not exist');
            }
        }
    }

    public function deleteComments($id)
    {
        $this->tableGateway->delete(array('id' => $id));
    }
}