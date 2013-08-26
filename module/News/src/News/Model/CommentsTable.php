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
        if( !$GLOBALS['isAdmin'] ) $sel->where(array('status' => 1));
        $sel->where(array('nid' => $nid));
        //if( $GLOBALS['isAdmin'] ) $sel->order('status');
        $sel->order('comments.dt DESC');
        //echo $sel->getSqlString();
        $resultSet = $this->tableGateway->selectWith($sel);
        return $resultSet;
    }

    public function getComments($id, $nid)
    {
        $id  = (int) $id;
        $nid  = (int) $nid;
        $sel = $this->tableGateway->getSql()->select();
        $sel->columns(array('id', 'dt' => new Expression('DATE_FORMAT(dt, "%d.%m.%Y %H:%i:%s")'), 'name', 'email', 'text', 'pict', 'nid', 'status'), false);
        if( !$GLOBALS['isAdmin'] ) $sel->where(array('status' => 1));
        $sel->where(array('id' => $id, 'nid' => $nid));
        //echo $sel->getSqlString();
        $rowset = $this->tableGateway->selectWith($sel);
        $row = $rowset->current();
        if (!$row) {
            throw new \Exception("Could not find row $id with parent id $nid");
        }
        return $row;
    }

    public function adoptPict($pict){
        $filecontent = file_get_contents($pict['tmp_name']);
        echo "mystrlen=" . strlen($filecontent);
        $im = imagecreatefromstring($filecontent);
        if( !$im ) return null;
        $wth = imagesx($im); $hgt = imagesy($im);
        echo "wth=$wth hgt=$hgt";
        if( $wth<=320 && $hgt<=240 ){
            imagedestroy($im);
            return $filecontent;
        }
        $k = ($wth/$hgt<320/240)?(240/$hgt):(320/$wth);
        $wth2 = $wth*$k; $hgt2 = $hgt*$k;
        echo "k=$k wth2=$wth2 hgt2=$hgt2";
        $im2 = imagecreatetruecolor($wth2, $hgt2);
        imagecopyresampled($im2, $im, 0, 0, 0, 0, $wth2, $hgt2, $wth, $hgt);
        $ret = null;
        if( imagegif($im2, "data/img/tmp.gif") ){
            $ret = file_get_contents("data/img/tmp.gif");
        }
        imagedestroy($im);
        imagedestroy($im2);
        return $ret;
    }

    public function saveComments(Comments $Comments, $id=0, $nid=0, $ispict=0)
    {
        $data = array(
            'name'  => $Comments->name,
            'email'  => $Comments->email,
            'text'  => $Comments->text,
            'status' => $Comments->status,
        );

        if( $Comments->pict['error']==0 ){
            $data['pict'] = $this->adoptPict($Comments->pict);
        }elseif ( !$ispict ) $data['pict'] = null;
        
        if ($id == 0) {
            $data['dt'] = $Comments->dt;
            $data['nid'] = $nid; 
            $this->tableGateway->insert($data);
        } else {
            if ($this->getComments($id, $nid)) {
                $this->tableGateway->update($data, array('id' => $id, 'nid' => $nid));
            } else {
                throw new \Exception('Comments id does not exist');
            }
        }
    }

    public function deleteComments($id, $nid)
    {
        $this->tableGateway->delete(array('id' => $id, 'nid' => $nid));
    }
}