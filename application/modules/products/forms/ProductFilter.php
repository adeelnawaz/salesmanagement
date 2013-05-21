<?php

class Products_Form_ProductFilter extends Zend_Form {

    public function init() {
        $view = new Zend_View();

        $company = new Zend_Form_Element_Text('company');
        $company->addFilter('StripTags')
                ->addFilter('StringTrim')
                ->setAttrib('class', 'inp-form')
                ->setDecorators(array('ViewHelper', 'Errors'));

        $product_model = new Zend_Form_Element_Text('product_model');
        $product_model->addFilter('StripTags')
                ->addFilter('StringTrim')
                ->setAttrib('class', 'inp-form')
                ->setDecorators(array('ViewHelper', 'Errors'));

        $this->addElements(array($company, $product_model));
    }

}

