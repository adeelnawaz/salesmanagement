<?php

class Application_Model_DbTable_AdminVars extends Zend_Db_Table_Abstract {

    protected $_name = 'admin_vars';

    public function getAdminVars() {
        $select = $this->select();
        $result = $this->_db->fetchAll($select);
        $return = array();
        foreach ($result as $value) {
            $return[$value['name']] = $value['value'];
        }
        
        return $return;
    }

}

