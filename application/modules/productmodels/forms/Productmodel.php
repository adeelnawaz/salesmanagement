<?php

class Productmodels_Form_Productmodel extends Zend_Form {

    public function init() {
        $company_id = new Zend_Form_Element_Select('company_id');
        $company_id->setRequired(TRUE)
                ->setAttrib('class', 'styledselect_form_1')
                ->setDecorators(array('ViewHelper', 'Errors'));

        $model_number = new Zend_Form_Element_Text('model_number');
        $model_number->setRequired(TRUE)
                ->addFilter('StripTags')
                ->addFilter('StringTrim')
                ->setAttrib('class', 'inp-form')
                ->setDecorators(array('ViewHelper', 'Errors'));

        $name = new Zend_Form_Element_Text('name');
        $name->addFilter('StripTags')
                ->addFilter('StringTrim')
                ->setAttrib('class', 'inp-form')
                ->setDecorators(array('ViewHelper', 'Errors'));

        $price = new Zend_Form_Element_Text('price');
        $price->setRequired(TRUE)
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

        $this->addElements(array($company_id, $model_number, $name, $price, $status));
    }

}

