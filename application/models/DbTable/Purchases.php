<?php

class Application_Model_DbTable_Purchases extends Zend_Db_Table_Abstract {

    protected $_name = 'purchases';

    function getPurchaseIdsBySupplierId($supplierId) {
        $select = $this->select()->from($this->_name, 'id')->where("supplier_id = ?", $supplierId);
        return $this->_db->fetchCol($select);
    }

    function deletePurchasesByIds($purchaseIds) {
        $where = $this->_db->quoteInto("id IN (?)", $purchaseIds);
        return $this->delete($where);
    }

    function getPurchasesPaginatedQuery($filters, $sort, $order) {
        $select = $this->_db->select();
        $select->from($this->_name, array('*', '(payable_amount - concession - payed_amount) AS remaining_balance'))
                ->joinLeft('suppliers', "$this->_name.supplier_id = suppliers.id", "CONCAT(first_name,' ',last_name) AS supplier_name")
                ->joinLeft('products', "products.sp_type = 'purchase' AND products.sp_id = $this->_name.id", array("SUM(products.count) AS products_count"))
                ->group("$this->_name.id");

        if (!empty($sort) && !empty($order)) {
            $select->order("$sort $order");
        } else {
            $select->order('created_at DESC');
        }
        if (!empty($filters['supplier_id'])) {
            $select->where("supplier_id = ?", $filters['supplier_id']);
        }
        if (!empty($filters['supplier_name'])) {
            $select->where("CONCAT(first_name,' ',last_name) LIKE ?", "%{$filters['supplier_name']}%");
        }
        if (!empty($filters['created_at_from'])) {
            $select->where("DATE($this->_name.created_at) >= ?", $filters['created_at_from']);
        }
        if (!empty($filters['created_at_to'])) {
            $select->where("DATE($this->_name.created_at) <= ?", $filters['created_at_to']);
        }
        if ($filters['type'] == 'purchase') {
            $select->where('payable_amount > 0');
        } else if ($filters['type'] == 'payment') {
            $select->where('payable_amount = 0');
        }

        return $select;
    }

    function getPurchasesStatsBySupplierId($supplierId) {
        $select = $this->_db->select()
                ->from($this->_name, array("COUNT($this->_name.id) AS purchases_count", 'SUM(payable_amount) AS payable_amount', 'SUM(payed_amount) AS payed_amount', "MAX($this->_name.created_at) as last_purchase"))
                ->joinLeft("products", "products.sp_type = 'purchase' AND products.sp_id = $this->_name.id", 'SUM(count) as products_count')
                ->where("supplier_id = ?", $supplierId);
        return $this->_db->fetchRow($select);
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

    function getPurchaseById($purchaseId) {
        return $this->find($purchaseId)->current();
    }

    function getPurchaseDetailsById($purchaseId) {
        $select = $this->_db->select();
        $select->from($this->_name)
                ->join('suppliers', "$this->_name.supplier_id = suppliers.id", "CONCAT(first_name,' ',last_name) AS supplier_name")
                ->where("$this->_name.id = ?", $purchaseId);

        return $this->_db->fetchRow($select);
    }

    function getPurchaseByProductId($productId) {
        $select = $this->_db->select();
        $select->from($this->_name)
                ->join('products', "$this->_name.id = products.purchase_id", '')
                ->where('products.id = ?', $productId);

        return $this->_db->fetchRow($select);
    }

    function getPurchasesByProductIds($productIds) {
        $select = $this->_db->select();
        $select->from($this->_name,'id')
                ->join('products', "$this->_name.id = products.purchase_id", '')
                ->where('products.id IN (?)', $productIds);

        return $this->_db->fetchCol($select);
    }

}

