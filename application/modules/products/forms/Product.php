<?php

class Products_Form_Product extends Zend_Form {

    public function init() {
        $firstName = new Zend_Form_Element_Text('first_name');
        $firstName->setRequired(TRUE)
                ->addFilter('StripTags')
                ->addFilter('StringTrim')
                ->setAttrib('class', 'inp-form')
                ->setDecorators(array('ViewHelper', 'Errors'));

        $lastName = new Zend_Form_Element_Text('last_name');
        $lastName->setRequired(TRUE)
                ->addFilter('StripTags')
                ->addFilter('StringTrim')
                ->setAttrib('class', 'inp-form')
                ->setDecorators(array('ViewHelper', 'Errors'));

        $phoneNumber = new Zend_Form_Element_Text('phone_number');
        $phoneNumber->setRequired(TRUE)
                ->addFilter('StripTags')
                ->addFilter('StringTrim')
                ->setAttrib('class', 'inp-form')
                ->setDecorators(array('ViewHelper', 'Errors'));

        $address = new Zend_Form_Element_Textarea('address');
        $address->setRequired(TRUE)
                ->addFilter('StripTags')
                ->addFilter('StringTrim')
                ->setAttrib('class', 'form-textarea')
                ->setDecorators(array('ViewHelper', 'Errors'));

        $other = new Zend_Form_Element_Textarea('other');
        $other->addFilter('StripTags')
                ->addFilter('StringTrim')
                ->setAttrib('class', 'form-textarea')
                ->setDecorators(array('ViewHelper', 'Errors'));

        $status = new Zend_Form_Element_Select('status');
        $status->addMultiOptions(array(
                    'enabled' => 'Enabled',
                    'disabled' => 'Disabled'
                ))
                ->setAttrib('class', 'styledselect_form_1')
                ->setDecorators(array('ViewHelper', 'Errors'));

        $this->addElements(array($firstName, $lastName, $phoneNumber, $address, $other, $status));
    }

}

