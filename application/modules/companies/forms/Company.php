<?php

class Companies_Form_Company extends Zend_Form {

    public function init() {
        $name = new Zend_Form_Element_Text('name');
        $name->setRequired(TRUE)
                ->addFilter('StripTags')
                ->addFilter('StringTrim')
                ->setAttrib('class', 'inp-form')
                ->setDecorators(array('ViewHelper', 'Errors'));

        $status = new Zend_Form_Element_Select('status');
        $status->addMultiOptions(array(
                    'enabled' => 'Enabled',
                    'disabled' => 'Disabled'
                ))
                ->setAttrib('class', 'styledselect_form_1')
                ->setDecorators(array('ViewHelper', 'Errors'));

        $this->addElements(array($name, $status));
    }

}

