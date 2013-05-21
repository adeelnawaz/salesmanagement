<?php

class Purchases_Form_Purchase extends Zend_Form {

    public function init() {
        $supplier_id = new Zend_Form_Element_Hidden('supplier_id');
        $supplier_id->setRequired(TRUE)
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

        $this->addElements(array($supplier_id, $payable_amount, $concession, $payed_amount));
    }

}

