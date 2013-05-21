<?php

class My_View_Helper_BaseUrl extends Zend_View_Helper_BaseUrl {

    public function baseUrl($file = NULL) {
        $baseUrl = parent::baseUrl($file);
        if (empty($baseUrl)) {
            $config = Zend_Registry::get('config');
            $baseUrl = $config->base_url;
        }
        return $baseUrl;
    }

}

?>
