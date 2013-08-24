<?php
namespace News\Model;

class Comments
{
    public $id;
    public $dt;
    public $name;
    public $email;
    public $text;
    public $pict;
    public $nid;
    public $status;

    public function exchangeArray($data)
    {
        $this->id     = (!empty($data['id'])) ? $data['id'] : null;
        $this->dt  = (!empty($data['dt'])) ? $data['dt'] : 'NOW()';
        $this->name  = (!empty($data['name'])) ? $data['name'] : null;
        $this->email  = (!empty($data['email'])) ? $data['email'] : null;
        $this->text  = (!empty($data['text'])) ? $data['text'] : null;
        $this->pict  = (!empty($data['pict'])) ? $data['pict'] : null;
        $this->nid  = (!empty($data['nid'])) ? $data['nid'] : null;
        $this->status  = (!empty($data['status'])) ? $data['status'] : null;
    }
    
    public function getArrayCopy()
    {
        return get_object_vars($this);
    }
    
}