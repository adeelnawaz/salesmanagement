<?php

class Application_Model_DbTable_Customers extends Zend_Db_Table_Abstract {

    protected $_name = 'customers';

    function getCustomerPaginatedQuery($filters, $sort, $order) {
        $select = $this->_db->select();
        $select->from($this->_name)
                ->joinLeft('sales', "sales.customer_id = $this->_name.id", array(
                    "SUM(payed_amount*(sales.on_hold='no')) - SUM(payable_amount*(sales.on_hold='no')) AS credit",
                    "SUM(CASE WHEN sales.on_hold='no' AND sales.payable_amount > 0 THEN 1 ELSE 0 END) AS sales_count",
                    "SUM(CASE WHEN sales.on_hold='yes' AND sales.payable_amount > 0 THEN 1 ELSE 0 END) AS sales_onhold",
                    "SUM(CASE WHEN sales.on_hold='no' AND sales.payable_amount = 0 THEN 1 ELSE 0 END) AS payments_count",
                    "SUM(CASE WHEN sales.on_hold='yes' AND sales.payable_amount = 0 THEN 1 ELSE 0 END) AS payments_onhold"
                ))
                ->group("$this->_name.id");

        if (!empty($sort) && !empty($order)) {
            $select->order("$sort $order");
        }
        if (!empty($filters['full_name'])) {
            $select->where("CONCAT(first_name,' ',last_name) LIKE ?", "%{$filters['full_name']}%");
        }
        if (!empty($filters['phone_number'])) {
            $select->where('phone_number LIKE ?', "%{$filters['phone_number']}%");
        }
        if (!empty($filters['address'])) {
            $select->where('address LIKE ?', "%{$filters['address']}%");
        }
        if (!empty($filters['other'])) {
            $select->where('other LIKE ?', "%{$filters['other']}%");
        }
        if (!empty($filters['status'])) {
            $select->where('status LIKE ?', "%{$filters['status']}%");
        }

        return $select;
    }

    function save($data) {
        if (isset($data['id'])) {
            $where = $this->_db->quoteInto("id = ?", $data['id']);
            $this->update($data, $where);
            return $data['id'];
        } else {
            return $this->insert($data);
        }
    }

    function getCustomer($customerId) {
        return $this->find($customerId)->current();
    }

    function getCustomerArray($customerId) {
        $select = $this->select()->where('id = ?', $customerId);
        return $this->_db->fetchRow($select);
    }

    function deleteCustomer($customerId) {
        $where = $this->_db->quoteInto("id = ?", $customerId);
        return $this->delete($where);
    }

    function getAutoComplete($term) {
        $select = $this->select()
                ->from($this->_name, array('id as value', "CONCAT(first_name,' ',last_name) AS label"))
                ->where("CONCAT(first_name,' ',last_name) LIKE ?", "%$term%")
                ->order('first_name ASC')
                ->limit(10);
        return $this->_db->fetchAll($select);
    }

}

