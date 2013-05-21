<?php

class My_View_Helper_Action extends Zend_View_Helper_BaseUrl {

    public function action() {
        return $request = Zend_Controller_Front::getInstance()->getRequest()->getActionName();
    }

}

?>
