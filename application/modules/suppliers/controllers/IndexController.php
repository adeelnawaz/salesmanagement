<?php

class Suppliers_IndexController extends Zend_Controller_Action {

    public function init() {
        $this->view->selectedTab = 'suppliers';
    }

    public function indexAction() {
        $this->view->selectedSubTab = 'list_suppliers';

        $pageRange = $this->_request->getParam('pagerange', 10);
        $pageNo = $this->_request->getParam('page', 1);
        $sort = $this->_request->getParam('sort');
        $order = $this->_request->getParam('order');

        $filter = new Suppliers_Form_SupplierFilter();
        $filters = array();

        if ($this->_request->isPost() && $filter->isValid($this->_request->getPost())) {
            $filters = $filter->getValues();
            $this->view->searched = TRUE;
        }

        $supplierModel = new Application_Model_DbTable_Suppliers();

        //Paginator...
        $select = $supplierModel->getSupplierPaginatedQuery($filters, $sort, $order);
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

    public function addAction() {
        $this->view->selectedSubTab = 'add_supplier';
        $this->view->placeholder('heading')->set($this->view->translate('Add supplier'));

        $supplierModel = new Application_Model_DbTable_Suppliers();

        $form = new Suppliers_Form_Supplier();

        //Changes in form
        $form->removeElement('status');

        if ($this->_request->isPost() && $form->isValid($this->_request->getPost())) {
            $data = $form->getValues();
            $supplierModel->save($data);

            $this->_redirect($this->view->url(array('module' => 'suppliers'), NULL, TRUE));
        }

        $this->view->form = $form;
    }

    public function editAction() {
        $this->view->placeholder('heading')->set($this->view->translate('Edit supplier'));

        $supplierId = $this->_request->getParam('id');

        $supplierModel = new Application_Model_DbTable_Suppliers();
        $supplier = $supplierModel->getSupplierArray($supplierId);

        $form = new Suppliers_Form_Supplier();
        $form->populate($supplier);

        if ($this->_request->isPost() && $form->isValid($this->_request->getPost())) {
            $data = $form->getValues();
            $data['id'] = $supplier['id'];
            $supplierModel->save($data);

            $this->_redirect($this->view->url(array('module' => 'suppliers'), NULL, TRUE));
        }

        $this->view->form = $form;

        $this->render('add');
    }

    public function clearhistoryAction() {
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender(TRUE);

        $supplierId = $this->_request->getParam('id');

        if ($this->_request->isPost()) {
            $deleteProducts = $this->_request->getPost('delete_products');

            $purchasesModel = new Application_Model_DbTable_Purchases();

            $purchaseIds = $purchasesModel->getPurchaseIdsBySupplierId($supplierId);

            if (!empty($purchaseIds)) {
                $purchasesModel->deletePurchasesByIds($purchaseIds);

                if ($deleteProducts == 'on') {
                    $productsModel = new Application_Model_DbTable_Products();
                    $productsModel->deleteProductsByPurchaseIds($purchaseIds);
                }
            }
        }

        $this->_redirect($this->view->url(array('module' => 'suppliers', 'action' => 'edit', 'id' => $supplierId), NULL, TRUE));
    }

    public function deleteAction() {
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender(TRUE);

        $supplierId = $this->_request->getParam('id');

        if ($this->_request->isPost()) {
            $deletePurchases = $this->_request->getPost('delete_purchases');
            $deleteProducts = $this->_request->getPost('delete_products');

            $suppliersModel = new Application_Model_DbTable_Suppliers();

            $suppliersModel->deleteSupplier($supplierId);

            if ($deletePurchases == 'on') {
                $purchasesModel = new Application_Model_DbTable_Purchases();

                $purchaseIds = $purchasesModel->getPurchaseIdsBySupplierId($supplierId);

                if (!empty($purchaseIds)) {
                    $purchasesModel->deletePurchasesByIds($purchaseIds);

                    if ($deleteProducts == 'on') {
                        $productsModel = new Application_Model_DbTable_Products();
                        $productsModel->deleteProductsByPurchaseIds($purchaseIds);
                    }
                }
            }
        }

        $this->_redirect($this->view->url(array('module' => 'suppliers'), NULL, TRUE));
    }

    function autocompleteAction() {
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender(TRUE);

        $term = $this->_request->getParam('term');

        $supplierModel = new Application_Model_DbTable_Suppliers();

        $suppliers = $supplierModel->getAutoComplete($term);

        /*$return = array();
        foreach ($suppliers as $supplier) {
            $return[$supplier['id']] = $supplier['supplier_name'];
        }*/

        //echo json_encode($return);
        echo json_encode($suppliers);
    }

}

