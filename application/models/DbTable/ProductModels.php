<?php

class Application_Model_DbTable_ProductModels extends Zend_Db_Table_Abstract {

    protected $_name = 'product_models';

    function getActiveProductModels() {
        $where = "status = 'enabled'";
        return $this->fetchAll($where);
    }

    function getActiveProductModelsWithStock() {
        $select = $this->select();
        $select->setIntegrityCheck(FALSE)
                ->from($this->_name)
                ->join('products', "products.product_model_id = $this->_name.id", "SUM(CASE WHEN sp_type = 'purchase' THEN `count` ELSE 0 END) - SUM(CASE WHEN sp_type = 'sale' THEN `count` ELSE 0 END) as stock")
                ->having('stock > 0')
                ->group("$this->_name.id");
        
        return $this->fetchAll($select);
    }

    function deleteProductModelsByCompanyId($companyId) {
        $where = $this->_db->quoteInto("company_id = ?", $companyId);
        return $this->delete($where);
    }

    function updateProductModelsByCompanyId($data, $companyId) {
        $where = $this->_db->quoteInto("company_id = ?", $companyId);
        return $this->update($data, $where);
    }

    function getProductmodelsPaginatedQuery($filters, $sort, $order) {
        $select = $this->_db->select();
        $select->from($this->_name)
                ->joinLeft('companies', "$this->_name.company_id = companies.id", "companies.name AS company")
                ->joinLeft('products', "products.product_model_id = $this->_name.id", array("COUNT(products.id) AS products_count"))
                ->group("$this->_name.id");

        if (!empty($sort) && !empty($order)) {
            $select->order("$sort $order");
        } else {
            $select->order('created_at DESC');
        }
        if (!empty($filters['company_id'])) {
            $select->where("$this->_name.company_id = ?", $filters['company_id']);
        }
        if (!empty($filters['company'])) {
            $select->where("companies.name LIKE ?", "%{$filters['company']}%");
        }
        if (!empty($filters['model_number'])) {
            $select->where("model_number LIKE ?", "%{$filters['model_number']}%");
        }
        if (!empty($filters['name'])) {
            $select->where("$this->_name.name LIKE ?", "%{$filters['name']}%");
        }
        if (!empty($filters['price'])) {
            if (strpos($filters['price'], '-') !== FALSE) {
                $price = explode('-', $filters['price'], 2);
                $select->where("price >= ?", $price[0]);
                $select->where("price <= ?", $price[1]);
            } else if (strpos($filters['price'], ',') !== FALSE) {
                $select->where("price IN (?)", explode(',', $filters['price']));
            } else {
                $select->where("price = ?", $filters['price']);
            }
        }
        if (!empty($filters['status'])) {
            $select->where("$this->_name.status = ?", $filters['status']);
        }
        if (!empty($filters['created_at_from'])) {
            $select->where("DATE($this->_name.created_at) >= ?", $filters['created_at_from']);
        }
        if (!empty($filters['created_at_to'])) {
            $select->where("DATE($this->_name.created_at) <= ?", $filters['created_at_to']);
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

    function getProductModelArray($productmodelId) {
        $select = $this->select()->where("id = ?", $productmodelId);
        return $this->_db->fetchRow($select);
    }

    function deleteProductModel($productmodelId) {
        $where = $this->_db->quoteInto("id = ?", $productmodelId);
        return $this->delete($where);
    }

    function deleteProductModels($productmodelIds) {
        $where = $this->_db->quoteInto("id IN (?)", $productmodelIds);
        return $this->delete($where);
    }

}

