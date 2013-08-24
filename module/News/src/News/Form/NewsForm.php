<?php
namespace News\Form;

use Zend\Form\Form;

class NewsForm extends Form
{
    public function __construct($name = null)
    {
        parent::__construct('news');
        $this->setAttribute('method', 'post');
        $this->add(array(
            'name' => 'id',
            'type' => 'Hidden',
        ));
        $this->add(array(
            'name' => 'title',
            'type' => 'Text',
            'attributes' => array(
                'size' => '100',
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
            'name' => 'submit',
            'type' => 'Submit',
            'attributes' => array(
                'value' => 'Go',
                'id' => 'submitbutton',
            ),
        ));
    }
}