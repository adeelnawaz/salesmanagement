<?php

class My_View_Helper_AlertMessages extends Zend_View_Helper_Abstract {

    public function alertMessages() {
        //$messenger = Zend_Controller_Action_HelperBroker::getStaticHelper('FlashMessenger');
        //$namespace = $messenger->getNamespace();
        //$messages = $messenger->getMessages('error');
        $alertMessage = new Zend_Session_Namespace('alert_message');
        $messages = $alertMessage->messages;
        $messageType = $alertMessage->type;
        Zend_Session::namespaceUnset('alert_message');

        $messageClass = 'blue';
        switch ($messageType) {
            case 'error':$messageClass = 'red';
                break;
            case 'warning':$messageClass = 'yellow';
                break;
            case 'success':$messageClass = 'green';
                break;
        }
        ?>
        <?php if (!empty($messages)) { ?>
            <div class="message-<?php echo $messageClass; ?>">
                <table border="0" cellpadding="0" cellspacing="0" width="100%">
                    <tbody>
                        <?php foreach ($messages as $message) { ?>
                            <tr>
                                <td class="<?php echo $messageClass; ?>-left"><?php echo $message; ?></td>
                                <td class="<?php echo $messageClass; ?>-right"><a class="close-<?php echo $messageClass; ?>"><img src="images/table/icon_close_<?php echo $messageClass; ?>.gif" alt=""></a></td>
                            </tr>
                        <?php } ?>
                    </tbody>
                </table>
            </div>
            <?php
        }
    }

    public function setView(\Zend_View_Interface $view) {
        //parent::setView($view);
        $this->view = $view;
    }

}
?>
