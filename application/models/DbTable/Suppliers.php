<?php

class Application_Model_DbTable_Suppliers extends Zend_Db_Table_Abstract {

    protected $_name = 'suppliers';

    function getSupplierPaginatedQuery($filters, $sort, $order) {
        $select = $this->_db->select();
        $select->from($this->_name)
                ->joinLeft('purchases', "purchases.supplier_id = $this->_name.id", array(
                    "SUM(payed_amount) - SUM(payable_amount) AS credit",
                    "SUM(CASE WHEN purchases.payable_amount > 0 THEN 1 ELSE 0 END) AS purchases_count"
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

    function getSupplier($supplierId) {
        return $this->find($supplierId)->current();
    }

    function getSupplierArray($supplierId) {
        $select = $this->select()->where('id = ?', $supplierId);
        return $this->_db->fetchRow($select);
    }

    function deleteSupplier($supplierId) {
        $where = $this->_db->quoteInto("id = ?", $supplierId);
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

