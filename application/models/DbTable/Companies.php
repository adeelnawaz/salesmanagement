<?php

class Application_Model_DbTable_Companies extends Zend_Db_Table_Abstract {

    protected $_name = 'companies';

    function getCompanies() {
        return $this->fetchAll();
    }

    function getActiveCompanies() {
        $where = "status = 'enabled'";
        return $this->fetchAll($where);
    }

    function getCompaniesPaginatedQuery($filters, $sort, $order) {
        $select = $this->_db->select();
        $select->from($this->_name)
                ->joinLeft('product_models', "$this->_name.id = product_models.company_id", "COUNT(DISTINCT(product_models.id)) AS product_models")
                ->joinLeft('products', "product_models.id = products.product_model_id", "COUNT(DISTINCT(products.id)) AS products")
                ->group("$this->_name.id");

        if (!empty($sort) && !empty($order)) {
            $select->order("$sort $order");
        } else {
            $select->order("created_at DESC");
        }
        if (!empty($filters['created_at_from'])) {
            $select->where("DATE($this->_name.created_at) >= ?", $filters['created_at_from']);
        }
        if (!empty($filters['created_at_to'])) {
            $select->where("DATE($this->_name.created_at) <= ?", $filters['created_at_to']);
        }
        if (!empty($filters['name'])) {
            $select->where("$this->_name.name LIKE ?", "%{$filters['name']}%");
        }
        if (!empty($filters['status'])) {
            $select->where("$this->_name.status = ?", $filters['status']);
        }

        return $select;
    }

    function deleteCompany($companyId) {
        $where = $this->_db->quoteInto("id = ?", $companyId);
        return $this->delete($where);
    }

    function getCompany($companyId) {
        $where = $this->_db->quoteInto("id = ?", $companyId);
        return $this->fetchRow($where);
    }

    function getCompanyArray($companyId) {
        $select = $this->select()->where("id = ?", $companyId);
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

    function getAutoComplete($term) {
        $select = $this->select()
                ->from($this->_name, array('id AS value', "name AS label"))
                ->where("name LIKE ?", "%$term%")
                ->order('name ASC')
                ->limit(10);
        return $this->_db->fetchAll($select);
    }
    
    function getCompanyByProductModelId($productmodelId) {
        $select = $this->_db->select();
        $select->from($this->_name)
                ->join('product_models', "$this->_name.id = product_models.company_id", '')
                ->where('product_models.id = ?', $productmodelId);

        return $this->_db->fetchRow($select);
    }

    function getCompaniesByProductModelIds($productmodelIds) {
        $select = $this->_db->select();
        $select->from($this->_name,'id')
                ->join('product_models', "$this->_name.id = product_models.company_id", '')
                ->where('product_models.id IN (?)', $productmodelIds);

        return $this->_db->fetchCol($select);
    }

}

