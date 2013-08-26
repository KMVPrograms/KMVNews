<?php
namespace News\Form;

use Zend\Form\Form;

class CommentsForm extends Form
{
    public function __construct($name = null)
    {
        parent::__construct('news');
        $this->setAttribute('method', 'post');
        $this->setAttribute('enctype', 'multipart/form-data');
        $this->add(array(
            'name' => 'name',
            'type' => 'Text',
            'attributes' => array(
                'size' => '50',
            ),
        ));
        $this->add(array(
            'name' => 'email',
            'type' => 'text',
            'attributes' => array(
                'size' => '50',
            ),
        ));
        $this->add(array(
            'name' => 'text',
            'type' => 'Textarea',
            'attributes' => array(
                'cols' => '150',
                'rows' => '10',
            ),
        ));
        $this->add(array(
            'name' => 'pict',
            'type' => 'File',
            'attributes' => array(
                'size' => '60',
            ),
        ));
        $this->add(array(
            'name' => 'ispict',
            'type' => 'Hidden',
        ));
        if( $GLOBALS['isAdmin'] ){
            $this->add(array(
                'name' => 'status',
                'type' => 'CheckBox',
            ));
        }
        $this->add(array(
            'name' => 'submit',
            'type' => 'Submit',
            'attributes' => array(
                'value' => 'Go',
                'id' => 'submitbutton',
            ),
        ));
    }
}