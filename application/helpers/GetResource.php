<?php

class My_View_Helper_GetResource extends Zend_View_Helper_Abstract {

    public function getResource($file) {
        $filePath = realpath(APPLICATION_PATH . "/../public/$file");
        return $this->view->baseUrl() . "/$file?" . filemtime($filePath);
    }

    public function setView(\Zend_View_Interface $view) {
        $this->view = $view;
    }

}

?>
