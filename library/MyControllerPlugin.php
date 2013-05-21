<?php

class MyControllerPlugin extends Zend_Controller_Plugin_Abstract {

    public function routeShutdown(\Zend_Controller_Request_Abstract $request) {
        parent::routeShutdown($request);
        $requestUri = $request->getModuleName() . '/' . $request->getControllerName() . '/' . $request->getActionName();
        if ($requestUri != 'default/index/index') {
            if (!Zend_Auth::getInstance()->hasIdentity()) {
                $r = Zend_Controller_Action_HelperBroker::getStaticHelper('redirector');
                $r->gotoUrl('/')->redirectAndExit();
            }
        }
    }

}

?>
