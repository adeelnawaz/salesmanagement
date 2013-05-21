<?php

class Productmodels_Form_ProductmodelFilter extends Zend_Form {

    public function init() {
        $view = new Zend_View();

        $company = new Zend_Form_Element_Text('company');
        $company->addFilter('StripTags')
                ->addFilter('StringTrim')
                ->setAttrib('class', 'inp-form')
                ->setDecorators(array('ViewHelper', 'Errors'));

        $modelNumber = new Zend_Form_Element_Text('model_number');
        $modelNumber->addFilter('StripTags')
                ->addFilter('StringTrim')
                ->setAttrib('class', 'inp-form')
                ->setDecorators(array('ViewHelper', 'Errors'));

        $name = new Zend_Form_Element_Text('name');
        $name->addFilter('StripTags')
                ->addFilter('StringTrim')
                ->setAttrib('class', 'inp-form')
                ->setDecorators(array('ViewHelper', 'Errors'));

        $price = new Zend_Form_Element_Text('price');
        $price->addFilter('StripTags')
                ->addFilter('StringTrim')
                ->setAttrib('class', 'inp-form')
                ->setDecorators(array('ViewHelper', 'Errors'))
                ->setAttrib('placeholder', '( x-y ) | (x,y,z) | (x)');

        $status = new Zend_Form_Element_Select('status');
        $status->addMultiOptions(array(
                    '' => '',
                    'enabled' => 'Enabled',
                    'disabled' => 'Disabled'
                ))
                ->setAttrib('class', 'styledselect_form_1')
                ->setDecorators(array('ViewHelper', 'Errors'));

        $created_at_from = new Zend_Form_Element_Text('created_at_from');
        $created_at_from->addFilter('StripTags')
                ->addFilter('StringTrim')
                ->setAttrib('class', 'inp-form inp-form-half datepicker')
                ->setDecorators(array('ViewHelper', 'Errors'))
                ->setAttrib('placeholder', $view->translate('From'));

        $created_at_to = new Zend_Form_Element_Text('created_at_to');
        $created_at_to->addFilter('StripTags')
                ->addFilter('StringTrim')
                ->setAttrib('class', 'inp-form inp-form-half datepicker')
                ->setDecorators(array('ViewHelper', 'Errors'))
                ->setAttrib('placeholder', $view->translate('To'));

        $this->addElements(array($company, $modelNumber, $name, $price, $status, $created_at_from, $created_at_to));
    }

}

