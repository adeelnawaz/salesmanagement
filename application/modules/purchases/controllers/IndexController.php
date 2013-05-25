<?php

class Purchases_IndexController extends Zend_Controller_Action {

    public function init() {
        $this->view->selectedTab = 'purchases';
    }

    public function indexAction() {
        $this->view->selectedSubTab = 'list_purchases';

        $this->view->placeholder('heading')->set($this->view->translate("Purchases"));

        $supplierId = $this->_request->getParam('id');

        $pageRange = $this->_request->getParam('pagerange', 10);
        $pageNo = $this->_request->getParam('page', 1);
        $sort = $this->_request->getParam('sort');
        $order = $this->_request->getParam('order');

        $filter = new Purchases_Form_PurchaseFilter();
        $filters = array();

        $purchasesModel = new Application_Model_DbTable_Purchases();

        if ($this->_request->isPost() && $filter->isValid($this->_request->getPost())) {
            $filters = $filter->getValues();
            $this->view->searched = TRUE;
        }

        if (!empty($supplierId)) {
            $supplierModel = new Application_Model_DbTable_Suppliers();
            $supplier = $supplierModel->getSupplier($supplierId);
            $supplierName = $supplier->first_name . ' ' . $supplier->last_name;

            $this->view->placeholder('heading')->set($this->view->translate("%s's purchases", $supplierName));

            $filters['supplier_id'] = $supplierId;
            $purchasesStats = $purchasesModel->getPurchasesStatsBySupplierId($supplierId);
            $this->view->supplierPurchases = TRUE;
            $this->view->supplierId = $supplierId;
            $this->view->purchasesStats = $purchasesStats;
        }

        //Paginator...
        $select = $purchasesModel->getPurchasesPaginatedQuery($filters, $sort, $order);
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

    function addAction() {
        $supplierId = $this->_request->getParam('id');

        $this->view->selectedSubTab = 'add_purchase';
        $this->view->placeholder('heading')->set($this->view->translate('Add purchase'));

        $purchasesModel = new Application_Model_DbTable_Purchases();
        $productModelsModel = new Application_Model_DbTable_ProductModels();
        $companyModel = new Application_Model_DbTable_Companies();
        $producsModel = new Application_Model_DbTable_Products();

        $productModels = $productModelsModel->getActiveProductModels();
        $companies = $companyModel->getActiveCompanies();
        if (count($productModels) == 0 || count($companies) == 0) {
            $errorMessages = array();
            if (count($productModels) == 0) {
                $errorMessages[] = $this->view->translate('Please add a product model first');
            }
            if (count($companies) == 0) {
                $errorMessages[] = $this->view->translate('Please add a company first');
            }
            $this->view->errorMessages = $errorMessages;
            return;
        }

        $form = new Purchases_Form_Purchase();

        if ($this->_request->isPost() && $form->isValid($this->_request->getPost())) {
            $createdAt = Zend_Date::now()->toString('yyyy-MM-dd HH:mm:ss');
            $data = $form->getValues();
            $data['created_at'] = $createdAt;

            $purchaseProductCompany = $this->_request->getPost('purchase_product_company');
            $purchaseProductModel = $this->_request->getPost('purchase_product_model');
            $purchaseProductCount = $this->_request->getPost('purchase_product_count');
            $purchaseProductPrice = $this->_request->getPost('purchase_product_price');

            $purchaseProducts = $this->getPurchaseProducts($purchaseProductCompany, $purchaseProductModel, $purchaseProductCount, $purchaseProductPrice);

            if (!empty($purchaseProducts)) {
                $purchaseId = $purchasesModel->save($data);

                foreach ($purchaseProducts as $purchaseProduct) {
                    $product = array(
                        'count' => $purchaseProduct['count'],
                        'product_model_id' => $purchaseProduct['product_model_id'],
                        'unit_price' => $purchaseProduct['unit_price'],
                        'sp_type' => 'purchase',
                        'sp_id' => $purchaseId,
                        'created_at' => $createdAt
                    );
                    $producsModel->save($product);
                }
                $this->_redirect($this->view->url(array('module' => 'purchases', 'id' => (!empty($supplierId) ? $supplierId : NULL)), NULL, TRUE));
            }
        }
        if ($this->_request->isPost()) {//Form not valid
            $supplierId = $this->_request->getPost('supplier_id');

            $purchaseProductCompany = $this->_request->getPost('purchase_product_company');
            $purchaseProductModel = $this->_request->getPost('purchase_product_model');
            $purchaseProductCount = $this->_request->getPost('purchase_product_count');
            $purchaseProductPrice = $this->_request->getPost('purchase_product_price');

            $purchaseProducts = $this->getPurchaseProducts($purchaseProductCompany, $purchaseProductModel, $purchaseProductCount, $purchaseProductPrice);
            $this->view->purchaseProducts = $purchaseProducts;
        }

        if (!empty($supplierId)) {
            $supplierModel = new Application_Model_DbTable_Suppliers();
            $supplier = $supplierModel->getSupplier($supplierId);
            $supplierName = $supplier->first_name . ' ' . $supplier->last_name;

            $form->supplier_id->setValue($supplier->id);

            $this->view->supplierName = $supplierName;
            $this->view->supplierPurchases = TRUE;
        }

        $this->view->form = $form;
        $this->view->productModels = $productModels;
        $this->view->companies = $companies;
    }

    function getPurchaseProducts($companies, $models, $count, $prices) {
        $products = array();

        if (count($models) === count($count) && count($count) == count($prices)) {
            for ($i = 0; $i < count($models); $i++) {
                $products[] = array(
                    'company_id' => $companies[$i],
                    'product_model_id' => $models[$i],
                    'count' => $count[$i],
                    'unit_price' => $prices[$i]
                );
            }
        }

        return $products;
    }

    function toggleholdAction() {
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender(TRUE);

        $purchaseId = $this->_request->getParam('id');

        $purchasesModel = new Application_Model_DbTable_Purchases();
        $purchase = $purchasesModel->getPurchaseById($purchaseId);

        $data = array(
            'id' => $purchaseId,
            'on_hold' => $purchase->on_hold == 'no' ? 'yes' : 'no'
        );

        $purchasesModel->save($data);

        $this->_redirect($this->view->url(array('module' => 'products', 'id' => $purchaseId), NULL, TRUE));
    }

    public function deleteAction() {
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender(TRUE);

        $purchaseId = $this->_request->getParam('id');

        if (!empty($purchaseId) && $this->_request->isPost()) {
            $purchasesModel = new Application_Model_DbTable_Purchases();

            $purchasesModel->deletePurchasesByIds($purchaseId);

            $productsModel = new Application_Model_DbTable_Products();

            $productsModel->deleteProductsByPurchaseIds($purchaseId);
        }

        $this->_redirect($this->view->url(array('module' => 'purchases'), NULL, TRUE));
    }

    function addpaymentAction() {
        $supplierId = $this->_request->getParam('id');

        $this->view->selectedSubTab = 'add_purchase_payment';
        $this->view->placeholder('heading')->set($this->view->translate('Add purchase payment'));

        $purchasesModel = new Application_Model_DbTable_Purchases();

        $form = new Purchases_Form_Payment();

        if ($this->_request->isPost() && $form->isValid($this->_request->getPost())) {
            $createdAt = Zend_Date::now()->toString('yyyy-MM-dd HH:mm:ss');
            $data = $form->getValues();
            $data['created_at'] = $createdAt;

            $purchasesModel->save($data);

            $this->_redirect($this->view->url(array('module' => 'purchases', 'id' => (!empty($supplierId) ? $supplierId : NULL)), NULL, TRUE));
        }
        if ($this->_request->isPost()) {//Form not valid
            $supplierId = $this->_request->getPost('supplier_id');
        }

        if (!empty($supplierId)) {
            $supplierModel = new Application_Model_DbTable_Suppliers();
            $supplier = $supplierModel->getSupplier($supplierId);
            $supplierName = $supplier->first_name . ' ' . $supplier->last_name;

            $form->supplier_id->setValue($supplier->id);

            $this->view->supplierName = $supplierName;
        }

        $this->view->form = $form;
    }

    public function detailAction() {
        $purchaseId = $this->_request->getParam('id');
        $supplierId = $this->_request->getParam('supplier_id');

        $pageRange = $this->_request->getParam('pagerange', 10);
        $pageNo = $this->_request->getParam('page', 1);
        $sort = $this->_request->getParam('sort');
        $order = $this->_request->getParam('order');

        $productsModel = new Application_Model_DbTable_Products();

        if (!empty($supplierId)) {
            $supplierModel = new Application_Model_DbTable_Suppliers();
            $purchasesModel = new Application_Model_DbTable_Purchases();

            $supplier = $supplierModel->getSupplier($supplierId);
            $supplierName = $supplier->first_name . ' ' . $supplier->last_name;

            $this->view->placeholder('heading')->set($this->view->translate("%s's products", $supplierName));

            $filters = array('supplier_id' => $supplierId, 'sp_type' => 'purchase');
            $purchasesStats = $purchasesModel->getPurchasesStatsBySupplierId($supplierId);
            $this->view->supplierProducts = TRUE;
            $this->view->supplierId = $supplierId;
            $this->view->purchasesStats = $purchasesStats;
        } else {
            $purchasesModel = new Application_Model_DbTable_Purchases();
            $purchase = $purchasesModel->getPurchaseDetailsById($purchaseId);

            if ($purchase['payable_amount'] > 0) {
                $this->view->placeholder('heading')->set($this->view->translate("Purchase details"));
            } else {
                $this->view->placeholder('heading')->set($this->view->translate("Payment details"));
            }

            $filters = array('sp_id' => $purchaseId, 'sp_type' => 'purchase');
            $this->view->purchase = $purchase;
            $this->view->purchaseId = $purchase['id'];
        }

        //Paginator...
        $select = $productsModel->getSPProductPaginatedQuery($filters, $sort, $order);
        $paginatorAdapter = new Zend_Paginator_Adapter_DbSelect($select);
        $paginator = new Zend_Paginator($paginatorAdapter);
        $paginator->setItemCountPerPage($pageRange);
        $paginator->setCurrentPageNumber($pageNo);
        //...Paginator

        $this->view->paginator = $paginator;
        $this->view->sort = $sort;
        $this->view->order = $order;
    }

}