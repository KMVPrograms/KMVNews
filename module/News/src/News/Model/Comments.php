<?php
namespace News\Model;

use Zend\InputFilter\Factory as InputFactory;
use Zend\InputFilter\InputFilter;
use Zend\InputFilter\FileInput;
use Zend\InputFilter\InputFilterAwareInterface;
use Zend\InputFilter\InputFilterInterface;
use Zend\Db\Sql\Expression;
use Zend\Validator\Regex;

class Comments implements InputFilterAwareInterface
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
        $this->dt  = (!empty($data['dt'])) ? $data['dt'] : new Expression('NOW()');
        $this->name  = (!empty($data['name'])) ? $data['name'] : null;
        $this->email  = (!empty($data['email'])) ? $data['email'] : null;
        $this->text  = (!empty($data['text'])) ? $data['text'] : null;
        $this->pict  = (!empty($data['pict'])) ? $data['pict'] : null;
        $this->nid  = (!empty($data['nid'])) ? $data['nid'] : null;
        $this->status  = (!empty($data['status'])) ? $data['status'] : 0;
    }
    
    public function getArrayCopy()
    {
        return get_object_vars($this);
    }
    
    public function setInputFilter(InputFilterInterface $inputFilter)
    {
        throw new \Exception("Not used");
    }
    
    public function getInputFilter()
    {
        if (!$this->inputFilter) {
            $inputFilter = new InputFilter();
            $factory     = new InputFactory();

            $reg = (new Regex('#^[a-z0-9]+$#i'));
            $reg->setMessage('Должно содержать только цифры и буквы латинского алфавита');
            $inputFilter->add($factory->createInput(array(
                'name'     => 'name',
                'required' => true,
                'filters'  => array(
                    array('name' => 'StringTrim'),
                ),
                'validators' => array(
                    $reg
                ),
            )));

            /*
            $inputFilter->add($factory->createInput(array(
                'name'     => 'status',
                'required' => true,
                'validators' => array( new Regex('#^[01]$#') ),
            )));
            */
            
            $inputFilter->add($factory->createInput(array(
                'name'     => 'email',
                'required' => true,
                'filters'  => array(
                    array('name' => 'StringTrim'),
                ),
                'validators' => array(
                    array(
                        'name'    => 'EmailAddress',
                    ),
                ),
            )));

            $inputFilter->add($factory->createInput(array(
                'name'     => 'text',
                'required' => true,
                'filters'  => array(
                    array('name' => 'StringTrim'),
                    array('name' => 'StripTags')
                ),
            )));

            $this->inputFilter = $inputFilter;
        }

        return $this->inputFilter;
    }
}