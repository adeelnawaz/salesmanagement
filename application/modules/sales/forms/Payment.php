<?php

class Sales_Form_Payment extends Zend_Form {

    public function init() {
        $customer_id = new Zend_Form_Element_Hidden('customer_id');
        $customer_id->setRequired(TRUE)
                ->addFilter('StripTags')
                ->addFilter('StringTrim')
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

        $this->addElements(array($customer_id, $payed_amount, $on_hold));
    }

}

