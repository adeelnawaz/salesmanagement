<?php

class Application_Model_DbTable_Sales extends Zend_Db_Table_Abstract {

    protected $_name = 'sales';

    function getSaleIdsByCustomerId($customerId) {
        $select = $this->select()->from($this->_name, 'id')->where("customer_id = ?", $customerId);
        return $this->_db->fetchCol($select);
    }

    function deleteSalesByIds($saleIds) {
        $where = $this->_db->quoteInto("id IN (?)", $saleIds);
        return $this->delete($where);
    }

    function getSalesPaginatedQuery($filters, $sort, $order) {
        $select = $this->_db->select();
        $select->from($this->_name, array('*', '(payable_amount - concession - payed_amount) AS remaining_balance'))
                ->joinLeft('customers', "$this->_name.customer_id = customers.id", "CONCAT(first_name,' ',last_name) AS customer_name")
                ->joinLeft('products', "products.sp_type = 'sale' AND products.sp_id = $this->_name.id", array("SUM(products.count) AS products_count"))
                ->group("$this->_name.id");

        if (!empty($sort) && !empty($order)) {
            $select->order("$sort $order");
        } else {
            $select->order('created_at DESC');
        }
        if (!empty($filters['customer_id'])) {
            $select->where("customer_id = ?", $filters['customer_id']);
        }
        if (!empty($filters['customer_name'])) {
            $select->where("CONCAT(first_name,' ',last_name) LIKE ?", "%{$filters['customer_name']}%");
        }
        if (!empty($filters['created_at_from'])) {
            $select->where("DATE($this->_name.created_at) >= ?", $filters['created_at_from']);
        }
        if (!empty($filters['created_at_to'])) {
            $select->where("DATE($this->_name.created_at) <= ?", $filters['created_at_to']);
        }
        if ($filters['type'] == 'sale') {
            $select->where('payable_amount > 0');
        } else if ($filters['type'] == 'payment') {
            $select->where('payable_amount = 0');
        }
        if (!empty($filters['on_hold'])) {
            $select->where('on_hold = ?', $filters['on_hold']);
        }

        return $select;
    }

    function getSalesStatsByCustomerId($customerId) {
        $select = $this->_db->select()
                ->from($this->_name, array("COUNT($this->_name.id) AS sales_count", 'SUM(payable_amount) AS payable_amount', 'SUM(payed_amount) AS payed_amount', "MAX($this->_name.created_at) as last_sale"))
                ->joinLeft("products", "products.sp_type = 'sale' AND products.sp_id = $this->_name.id", 'SUM(count) as products_count')
                ->where("customer_id = ?", $customerId)
                ->where("on_hold = 'no'");
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

    function getSaleById($saleId) {
        return $this->find($saleId)->current();
    }

    function getSaleDetailsById($saleId) {
        $select = $this->_db->select();
        $select->from($this->_name)
                ->join('customers', "$this->_name.customer_id = customers.id", "CONCAT(first_name,' ',last_name) AS customer_name")
                ->where("$this->_name.id = ?", $saleId);

        return $this->_db->fetchRow($select);
    }

    function getSaleByProductId($productId) {
        $select = $this->_db->select();
        $select->from($this->_name)
                ->join('products', "$this->_name.id = products.sale_id", '')
                ->where('products.id = ?', $productId);

        return $this->_db->fetchRow($select);
    }

    function getSalesByProductIds($productIds) {
        $select = $this->_db->select();
        $select->from($this->_name, 'id')
                ->join('products', "$this->_name.id = products.sale_id", '')
                ->where('products.id IN (?)', $productIds);

        return $this->_db->fetchCol($select);
    }

}

