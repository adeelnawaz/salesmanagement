<?php

class Purchases_Form_Payment extends Zend_Form {

    public function init() {
        $supplier_id = new Zend_Form_Element_Hidden('supplier_id');
        $supplier_id->setRequired(TRUE)
                ->addFilter('StripTags')
                ->addFilter('StringTrim')
                ->setDecorators(array('ViewHelper', 'Errors'));

        $payed_amount = new Zend_Form_Element_Text('payed_amount');
        $payed_amount->setRequired(TRUE)
                ->addFilter('StripTags')
                ->addFilter('StringTrim')
                ->setAttrib('class', 'inp-form')
                ->setDecorators(array('ViewHelper', 'Errors'));

        $this->addElements(array($supplier_id, $payed_amount));
    }

}

