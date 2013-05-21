<?php

class Default_IndexController extends Zend_Controller_Action {

    private $_flashMessenger = NULL;

    public function init() {
        $this->_flashMessenger = $this->_helper->getHelper('flashMessenger');
        //$this->initView();

        $this->_helper->layout->setLayout('login/layout');
    }

    public function indexAction() {
        // action body
        if (Zend_Auth::getInstance()->getIdentity() === TRUE) {
            $adminVars = Zend_Registry::get('admin_vars');
            $defaultUrl = $this->view->url(array('module' => 'customer'), NULL, TRUE);
            if (isset($adminVars['default_url'])) {
                @list($action, $controller, $module) = array_reverse(explode('/', $adminVars['default_url']));
                $params = array();
                if (!empty($action)) {
                    $params['action'] = $action;
                }
                if (!empty($controller)) {
                    $params['controller'] = $controller;
                }
                if (!empty($module)) {
                    $params['module'] = $module;
                }
                $defaultUrl = $this->view->url($params, NULL, TRUE);
            }
            $this->_redirect($defaultUrl);
        } else {
            $auth = Zend_Auth::getInstance();
            $auth->clearIdentity();

            $form = new Default_Form_LoginForm();

            if ($this->_request->isPost() && $form->isValid($this->_request->getPost())) {
                $loginName = $this->_request->getPost('login_name');
                $password = $this->_request->getPost('password');

                $adminVars = Zend_Registry::get('admin_vars');

                if ($adminVars['login_name'] === $loginName && $adminVars['password'] === $password) {
                    $auth->getStorage()->write(TRUE);
                    if ($this->_request->getPost('remember_me') == 'on') {
                        $seconds = 60 * 60 * 24 * 7;
                        Zend_Session::rememberMe($seconds);
                        $namespace = new Zend_Session_Namespace('Zend_Auth');
                        $namespace->setExpirationSeconds($seconds); //remember for a week
                    }
                } else {
                    $this->view->addAlertMessage('Invalid credentials', 'error');
                    $this->_redirect('/');
                }

                $this->_redirect($this->view->url(array(), NULL, TRUE));
            }

            $this->view->form = $form;
        }
    }

    public function forgotpasswordAction() {
        
    }

    public function logoutAction() {
        Zend_Auth::getInstance()->clearIdentity();

        $this->_redirect($this->url(array(), NULL, TRUE));
    }

}

