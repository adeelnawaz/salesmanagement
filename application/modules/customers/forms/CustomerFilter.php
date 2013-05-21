<?php

class Customers_Form_CustomerFilter extends Zend_Form {

    public function init() {
        $view = new Zend_View();

        $fullName = new Zend_Form_Element_Text('full_name');
        $fullName->addFilter('StripTags')
                ->addFilter('StringTrim')
                ->setAttrib('class', 'inp-form')
                ->setDecorators(array('ViewHelper', 'Errors'))
                ->setAttrib('placeholder', $view->translate('Full name'));

        $phoneNumber = new Zend_Form_Element_Text('phone_number');
        $phoneNumber->addFilter('StripTags')
                ->addFilter('StringTrim')
                ->setAttrib('class', 'inp-form')
                ->setDecorators(array('ViewHelper', 'Errors'));

        $address = new Zend_Form_Element_Text('address');
        $address->addFilter('StripTags')
                ->addFilter('StringTrim')
                ->setAttrib('class', 'inp-form')
                ->setDecorators(array('ViewHelper', 'Errors'));

        $other = new Zend_Form_Element_Text('other');
        $other->addFilter('StripTags')
                ->addFilter('StringTrim')
                ->setAttrib('class', 'inp-form')
                ->setDecorators(array('ViewHelper', 'Errors'));

        $status = new Zend_Form_Element_Select('status');
        $status->addMultiOptions(array(
                    '' => '',
                    'enabled' => 'Enabled',
                    'disabled' => 'Disabled'
                ))
                ->setAttrib('class', 'styledselect_form_1')
                ->setDecorators(array('ViewHelper', 'Errors'));

        $this->addElements(array($fullName, $phoneNumber, $address, $other, $status));
    }

}

