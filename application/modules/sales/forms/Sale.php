<?php

class Sales_Form_Sale extends Zend_Form {

    public function init() {
        $customer_id = new Zend_Form_Element_Hidden('customer_id');
        $customer_id->setRequired(TRUE)
                ->addFilter('StripTags')
                ->addFilter('StringTrim')
                ->setDecorators(array('ViewHelper', 'Errors'));

        $payable_amount = new Zend_Form_Element_Text('payable_amount');
        $payable_amount->setRequired(TRUE)
                ->addFilter('StripTags')
                ->addFilter('StringTrim')
                ->setAttrib('class', 'inp-form')
                ->setDecorators(array('ViewHelper', 'Errors'))
                ->setAttrib('readonly', 'readonly');

        $concession = new Zend_Form_Element_Text('concession');
        $concession->addFilter('StripTags')
                ->addFilter('StringTrim')
                ->setAttrib('class', 'inp-form')
                ->setDecorators(array('ViewHelper', 'Errors'));

        $payed_amount = new Zend_Form_Element_Text('payed_amount');
        $payed_amount->setRequired(TRUE)
                ->addFilter('StripTags')
                ->addFilter('StringTrim')
                ->setAttrib('class', 'inp-form')
                ->setDecorators(array('ViewHelper', 'Errors'));

        $on_hold = new Zend_Form_Element_Select('on_hold');
        $on_hold->addMultiOptions(array(
                    'no' => 'No',
                    'yes' => 'Yes'
                ))
                ->setAttrib('class', 'styledselect_form_1')
                ->setDecorators(array('ViewHelper', 'Errors'));

        $this->addElements(array($customer_id, $payable_amount, $concession, $payed_amount, $on_hold));
    }

}

