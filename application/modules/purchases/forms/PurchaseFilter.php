<?php

class Purchases_Form_PurchaseFilter extends Zend_Form {

    public function init() {
        $view = new Zend_View();

        $supplierName = new Zend_Form_Element_Text('supplier_name');
        $supplierName->addFilter('StripTags')
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
                    'purchase' => 'Purchase',
                    'payment' => 'Payment'
                ))
                ->setAttrib('class', 'styledselect_form_1')
                ->setDecorators(array('ViewHelper', 'Errors'));

        $this->addElements(array($supplierName, $created_at_from, $created_at_to, $type));
    }

}

