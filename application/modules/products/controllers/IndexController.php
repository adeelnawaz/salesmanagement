<?php

class Products_IndexController extends Zend_Controller_Action {

    public function init() {
        $this->view->selectedTab = 'products';
    }

    public function indexAction() {
        $this->view->placeholder('heading')->set($this->view->translate("Product stock"));

        $pageRange = $this->_request->getParam('pagerange', 10);
        $pageNo = $this->_request->getParam('page', 1);
        $sort = $this->_request->getParam('sort');
        $order = $this->_request->getParam('order');

        $filter = new Products_Form_ProductFilter();
        $filters = array();

        if ($this->_request->isPost() && $filter->isValid($this->_request->getPost())) {
            $filters = $filter->getValues();
            $this->view->searched = TRUE;
        }

        $productsModel = new Application_Model_DbTable_Products();

        //Paginator...
        $select = $productsModel->getProductPaginatedQuery($filters, $sort, $order);
        $paginatorAdapter = new Zend_Paginator_Adapter_DbSelect($select);
        $paginator = new Zend_Paginator($paginatorAdapter);
        $paginator->setItemCountPerPage($pageRange);
        $paginator->setCurrentPageNumber($pageNo);
        //...Paginator

        $this->view->paginator = $paginator;
        $this->view->sort = $sort;
        $this->view->order = $order;
        $this->view->filter = $filter;
    }

}

