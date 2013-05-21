<?php

class Application_Model_DbTable_Products extends Zend_Db_Table_Abstract {

    protected $_name = 'products';

    function deleteProductsBySaleIds($saleIds) {
        $where = $this->_db->quoteInto("sp_type = 'sale' AND sp_id IN (?)", $saleIds);
        return $this->delete($where);
    }

    function deleteProductsByPurchaseIds($purchaseIds) {
        $where = $this->_db->quoteInto("sp_type = 'purchase' AND sp_id IN (?)", $purchaseIds);
        return $this->delete($where);
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

    function getProductsBySaleId($saleId) {
        $where = $this->_db->quoteInto("sp_type = 'sale' AND sp_id = ?", $saleId);
        return $this->fetchAll($where);
    }

    function getProductsByPurchaseId($purchaseId) {
        $where = $this->_db->quoteInto("sp_type = 'purchase' AND sp_id = ?", $purchaseId);
        return $this->fetchAll($where);
    }

    function getProductPaginatedQuery($filters, $sort, $order) {
        $select = $this->_db->select();
        $select->from($this->_name, array(
                    "SUM(CASE WHEN sp_type = 'purchase' THEN `count` ELSE 0 END) as purchased",
                    "SUM(CASE WHEN sp_type = 'sale' THEN `count` ELSE 0 END) as sold",
                    "SUM(CASE WHEN sp_type = 'purchase' THEN `count` ELSE 0 END) - SUM(CASE WHEN sp_type = 'sale' THEN `count` ELSE 0 END) as stock",
                    "SUM(CASE WHEN sp_type = 'purchase' THEN `unit_price` * `count` ELSE 0 END) as sum_p_price",
                    "SUM(CASE WHEN sp_type = 'sale' THEN `unit_price` * `count` ELSE 0 END) as sum_s_price"
                ))
                ->joinLeft('product_models', "$this->_name.product_model_id = product_models.id", "CONCAT(model_number,' - ',product_models.name) AS product_model")
                ->joinLeft('companies', "product_models.company_id = companies.id", 'companies.name AS company')
                ->group("$this->_name.product_model_id");

        if (!empty($sort) && !empty($order)) {
            $select->order("$sort $order");
        } else {
            $select->order('stock DESC');
        }
        if (!empty($filters['company'])) {
            $select->where("companies.name LIKE ?", "%{$filters['company']}%");
        }
        if (!empty($filters['product_model'])) {
            $select->where("CONCAT(model_number,' - ',product_models.name) LIKE ?", "%{$filters['product_model']}%");
        }

        return $select;
    }

    function deleteProduct($productId) {
        $where = $this->_db->quoteInto("id = ?", $productId);
        return $this->delete($where);
    }

    function deleteProducts($productIds) {
        $where = $this->_db->quoteInto("id IN (?)", $productIds);
        return $this->delete($where);
    }

    function updateProductsBySaleIds($data, $saleId) {
        $where = $this->_db->quoteInto("sp_type = 'sale' AND sp_id = ?", $saleId);
        return $this->update($data, $where);
    }
    
    function updateProductsByPurchaseIds($data, $purchaseId) {
        $where = $this->_db->quoteInto("sp_type = 'purchase' AND sp_id = ?", $purchaseId);
        return $this->update($data, $where);
    }

    /* Gets paginated query for sale / purchase detail page */

    function getSPProductPaginatedQuery($filters, $sort, $order) {
        $select = $this->_db->select();
        $select->from($this->_name)
                ->joinLeft('product_models', "$this->_name.product_model_id = product_models.id", "CONCAT(model_number,' - ',product_models.name) AS product_model")
                ->joinLeft('companies', "product_models.company_id = companies.id", 'companies.name AS company');

        if (!empty($sort) && !empty($order)) {
            $select->order("$sort $order");
        } else {
            $select->order('company DESC');
        }
        if (!empty($filters['company'])) {
            $select->where("companies.name LIKE ?", "%{$filters['company']}%");
        }
        if (!empty($filters['product_model'])) {
            $select->where("CONCAT(model_number,' - ',product_models.name) LIKE ?", "%{$filters['product_model']}%");
        }
        if (!empty($filters['sp_id'])) {
            $select->where("$this->_name.sp_id = ?", $filters['sp_id']);
        }
        if (!empty($filters['sp_type'])) {
            $select->where("$this->_name.sp_type = ?", $filters['sp_type']);
        }

        return $select;
    }

}

