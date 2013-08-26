<?php
namespace News\Form;

use Zend\Form\Form;
use Zend\InputFilter\Factory as InputFactory;
use Zend\InputFilter\InputFilter;

class AdminForm extends Form
{
  
    public function __construct($name = null)
    {
        parent::__construct('news');
        $this->setAttribute('method', 'post');

        $this->add(array(
            'name' => 'username',
            'type' => 'Text',
            'attributes' => array(
                'size' => '50',
            ),
        ));
        
        $this->add(array(
            'name' => 'password',
            'type' => 'password',
            'attributes' => array(
                'size' => '50',
            ),
        ));

        $this->add(array(
            'name' => 'submit',
            'type' => 'Submit',
            'attributes' => array(
                'value' => 'Войти',
            ),
        ));

        $inputFilter = new InputFilter();
        $factory     = new InputFactory();

        $inputFilter->add($factory->createInput(array(
            'name'     => 'username',
            'filters'  => array(
                array('name' => 'StringTrim'),
            ),
            'required' => true,
            'validators' => array(
                array(
                    'name' => 'NotEmpty',
                    'options' => array(
                        'messages' => array(
                            'isEmpty' => 'имя пользователя обязательный пункт!'
                        )
                    )
                )
            ),
        )));

        $inputFilter->add($factory->createInput(array(
            'name'     => 'password',
            'filters'  => array(
                array('name' => 'StringTrim'),
            ),
            'required' => true,
            'validators' => array(
                array(
                    'name' => 'NotEmpty',
                    'options' => array(
                        'messages' => array(
                            'isEmpty' => 'пароль не может быть пустым!'
                        )
                    )
                )
            ),
        )));
        
        $this->setInputFilter($inputFilter);
    }
}