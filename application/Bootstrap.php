<?php

class Bootstrap extends Zend_Application_Bootstrap_Bootstrap {

    public function _initConfig() {
        $config = new Zend_Config_Ini(APPLICATION_PATH . '/configs/application.ini', APPLICATION_ENV);
        Zend_Registry::set('config', $config);
    }

    public function _initPaginator() {
        $this->bootstrap('view');
        $view = $this->getResource('view');
        $view->addScriptPath(APPLICATION_PATH . '/views');
    }

    public function _initDbAdapter() {
        $config = Zend_Registry::get('config');
        $db = Zend_Db::factory($config->resources->db);
        Zend_Db_Table::setDefaultAdapter($db);
    }

    public function _initAdminVars() {
        $adminVarsModel = new Application_Model_DbTable_AdminVars();
        $adminVars = $adminVarsModel->getAdminVars();
        Zend_Registry::set('admin_vars', $adminVars);
    }

    public function _initAutoloader() {
        Zend_Loader_Autoloader::getInstance()->setFallbackAutoloader(TRUE);
    }

    public function _initControllerPlugin() {
        $front = Zend_Controller_Front::getInstance();
        $front->registerPlugin(new MyControllerPlugin());
    }

}