<?php

class Sales_Form_SaleFilter extends Zend_Form {

    public function init() {
        $view = new Zend_View();

        $customerName = new Zend_Form_Element_Text('customer_name');
        $customerName->addFilter('StripTags')
                ->addFilter('StringTrim')
                ->setAttrib('class', 'inp-form')
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

        $type = new Zend_Form_Element_Select('type');
        $type->addMultiOptions(array(
                    '' => 'Type',
                    'sale' => 'Sale',
                    'payment' => 'Payment'
                ))
                ->setAttrib('class', 'styledselect_form_1')
                ->setDecorators(array('ViewHelper', 'Errors'));

        $onHold = new Zend_Form_Element_Select('on_hold');
        $onHold->addMultiOptions(array(
                    '' => '',
                    'yes' => 'Yes',
                    'no' => 'No'
                ))
                ->setAttrib('class', 'styledselect_form_1')
                ->setDecorators(array('ViewHelper', 'Errors'));

        $this->addElements(array($customerName, $created_at_from, $created_at_to, $type, $onHold));
    }

}

