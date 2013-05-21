<?php

class Default_Form_LoginForm extends Zend_Form {

    public function init() {
        $customername = new Zend_Form_Element_Text('login_name');
        $customername->addFilter('StringTrim')
                ->addFilter('StripTags')
                ->setRequired(TRUE)
                ->addDecorators(array('ViewHelper', 'Errors'));

        $password = new Zend_Form_Element_Password('password');
        $password->addFilter('StringTrim')
                ->addFilter('StripTags')
                ->setRequired(TRUE)
                ->addDecorators(array('ViewHelper', 'Errors'));

        $this->addElements(array($customername, $password));
    }

}