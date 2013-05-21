<?php

class My_View_Helper_AddAlertMessage extends Zend_View_Helper_Abstract {

    public function addAlertMessage($message, $type) {
        $alertMessage = new Zend_Session_Namespace('alert_message');
        $alertMessage->messages[] = $message;
        $alertMessage->type = $type;
    }

}

?>
