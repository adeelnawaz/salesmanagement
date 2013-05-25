<?php

class Sales_IndexController extends Zend_Controller_Action {

    public function init() {
        $this->view->selectedTab = 'sales';
    }

    public function indexAction() {
        $this->view->selectedSubTab = 'list_sales';

        $this->view->placeholder('heading')->set($this->view->translate("Sales"));

        $customerId = $this->_request->getParam('id');

        $pageRange = $this->_request->getParam('pagerange', 10);
        $pageNo = $this->_request->getParam('page', 1);
        $sort = $this->_request->getParam('sort');
        $order = $this->_request->getParam('order');

        $filter = new Sales_Form_SaleFilter();
        $filters = array();

        $salesModel = new Application_Model_DbTable_Sales();

        if ($this->_request->isPost() && $filter->isValid($this->_request->getPost())) {
            $filters = $filter->getValues();
            $this->view->searched = TRUE;
        }

        if (!empty($customerId)) {
            $customerModel = new Application_Model_DbTable_Customers();
            $customer = $customerModel->getCustomer($customerId);
            $customerName = $customer->first_name . ' ' . $customer->last_name;

            $this->view->placeholder('heading')->set($this->view->translate("%s's sales", $customerName));

            $filters['customer_id'] = $customerId;
            $salesStats = $salesModel->getSalesStatsByCustomerId($customerId);
            $this->view->customerSales = TRUE;
            $this->view->customerId = $customerId;
            $this->view->salesStats = $salesStats;
        }

        //Paginator...
        $select = $salesModel->getSalesPaginatedQuery($filters, $sort, $order);
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
        $customerId = $this->_request->getParam('id');

        $this->view->selectedSubTab = 'add_sale';
        $this->view->placeholder('heading')->set($this->view->translate('Add sale'));

        $salesModel = new Application_Model_DbTable_Sales();
        $productModelsModel = new Application_Model_DbTable_ProductModels();
        $companyModel = new Application_Model_DbTable_Companies();
        $producsModel = new Application_Model_DbTable_Products();

        $productModels = $productModelsModel->getActiveProductModelsWithStock();
        $companies = $companyModel->getActiveCompanies();
        if (count($productModels) == 0 || count($companies) == 0) {
            $errorMessages = array();
            $productModels = $productModelsModel->getActiveProductModels();
            if (count($productModels) == 0) {
                $errorMessages[] = $this->view->translate('Please add a product model first');
            } else {
                $errorMessages[] = $this->view->translate('Please add a product in stock first');
            }
            if (count($companies) == 0) {
                $errorMessages[] = $this->view->translate('Please add a company first');
            }
            $this->view->errorMessages = $errorMessages;
            return;
        }

        $form = new Sales_Form_Sale();

        if ($this->_request->isPost() && $form->isValid($this->_request->getPost())) {
            $createdAt = Zend_Date::now()->toString('yyyy-MM-dd HH:mm:ss');
            $data = $form->getValues();
            $data['created_at'] = $createdAt;

            $saleProductCompany = $this->_request->getPost('sale_product_company');
            $saleProductModel = $this->_request->getPost('sale_product_model');
            $saleProductCount = $this->_request->getPost('sale_product_count');
            $saleProductPrice = $this->_request->getPost('sale_product_price');

            $saleProducts = $this->getSaleProducts($saleProductCompany, $saleProductModel, $saleProductCount, $saleProductPrice);

            if (!empty($saleProducts)) {
                $saleId = $salesModel->save($data);

                foreach ($saleProducts as $saleProduct) {
                    $product = array(
                        'count' => $saleProduct['count'],
                        'product_model_id' => $saleProduct['product_model_id'],
                        'unit_price' => $saleProduct['unit_price'],
                        'sp_type' => 'sale',
                        'sp_id' => $saleId,
                        'created_at' => $createdAt
                    );
                    $producsModel->save($product);
                }
                $this->_redirect($this->view->url(array('module' => 'sales', 'id' => (!empty($customerId) ? $customerId : NULL)), NULL, TRUE));
            }
        }
        if ($this->_request->isPost()) {//Form not valid
            $customerId = $this->_request->getPost('customer_id');

            $saleProductCompany = $this->_request->getPost('sale_product_company');
            $saleProductModel = $this->_request->getPost('sale_product_model');
            $saleProductCount = $this->_request->getPost('sale_product_count');
            $saleProductPrice = $this->_request->getPost('sale_product_price');

            $saleProducts = $this->getSaleProducts($saleProductCompany, $saleProductModel, $saleProductCount, $saleProductPrice);
            $this->view->saleProducts = $saleProducts;
        }

        if (!empty($customerId)) {
            $customerModel = new Application_Model_DbTable_Customers();
            $customer = $customerModel->getCustomer($customerId);
            $customerName = $customer->first_name . ' ' . $customer->last_name;

            $form->customer_id->setValue($customer->id);

            $this->view->customerName = $customerName;
            $this->view->customerSales = TRUE;
        }

        $this->view->form = $form;
        $this->view->productModels = $productModels;
        $this->view->companies = $companies;
    }

    function getSaleProducts($companies, $models, $count, $prices) {
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

        $saleId = $this->_request->getParam('id');

        $salesModel = new Application_Model_DbTable_Sales();
        $sale = $salesModel->getSaleById($saleId);

        $data = array(
            'id' => $saleId,
            'on_hold' => $sale->on_hold == 'no' ? 'yes' : 'no'
        );

        $salesModel->save($data);

        $this->_redirect($this->view->url(array('module' => 'sales', 'action' => 'detail', 'id' => $saleId), NULL, TRUE));
    }

    public function deleteAction() {
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender(TRUE);

        $saleId = $this->_request->getParam('id');

        if (!empty($saleId) && $this->_request->isPost()) {
            $salesModel = new Application_Model_DbTable_Sales();

            $salesModel->deleteSalesByIds($saleId);

            $productsModel = new Application_Model_DbTable_Products();

            $productsModel->deleteProductsBySaleIds($saleId);
        }

        $this->_redirect($this->view->url(array('module' => 'sales'), NULL, TRUE));
    }

    function addpaymentAction() {
        $customerId = $this->_request->getParam('id');

        $this->view->selectedSubTab = 'add_sale_payment';
        $this->view->placeholder('heading')->set($this->view->translate('Add sale payment'));

        $salesModel = new Application_Model_DbTable_Sales();

        $form = new Sales_Form_Payment();

        if ($this->_request->isPost() && $form->isValid($this->_request->getPost())) {
            $createdAt = Zend_Date::now()->toString('yyyy-MM-dd HH:mm:ss');
            $data = $form->getValues();
            $data['created_at'] = $createdAt;

            $salesModel->save($data);

            $this->_redirect($this->view->url(array('module' => 'sales', 'id' => (!empty($customerId) ? $customerId : NULL)), NULL, TRUE));
        }
        if ($this->_request->isPost()) {//Form not valid
            $customerId = $this->_request->getPost('customer_id');
        }

        if (!empty($customerId)) {
            $customerModel = new Application_Model_DbTable_Customers();
            $customer = $customerModel->getCustomer($customerId);
            $customerName = $customer->first_name . ' ' . $customer->last_name;

            $form->customer_id->setValue($customer->id);

            $this->view->customerName = $customerName;
        }

        $this->view->form = $form;
    }

    public function detailAction() {
        $saleId = $this->_request->getParam('id');
        $customerId = $this->_request->getParam('customer_id');

        $pageRange = $this->_request->getParam('pagerange', 10);
        $pageNo = $this->_request->getParam('page', 1);
        $sort = $this->_request->getParam('sort');
        $order = $this->_request->getParam('order');

        $productsModel = new Application_Model_DbTable_Products();

        if (!empty($customerId)) {
            $customerModel = new Application_Model_DbTable_Customers();
            $salesModel = new Application_Model_DbTable_Sales();
            $customer = $customerModel->getCustomer($customerId);
            $customerName = $customer->first_name . ' ' . $customer->last_name;

            $this->view->placeholder('heading')->set($this->view->translate("%s's products", $customerName));

            $filters = array('customer_id' => $customerId, 'sp_type' => 'sale');
            $salesStats = $salesModel->getSalesStatsByCustomerId($customerId);
            $this->view->customerProducts = TRUE;
            $this->view->customerId = $customerId;
            $this->view->salesStats = $salesStats;
        } else {
            $salesModel = new Application_Model_DbTable_Sales();
            $sale = $salesModel->getSaleDetailsById($saleId);

            if ($sale['payable_amount'] > 0) {
                $this->view->placeholder('heading')->set($this->view->translate("Sale details"));
            } else {
                $this->view->placeholder('heading')->set($this->view->translate("Payment details"));
            }
            $filters = array('sp_id' => $saleId, 'sp_type' => 'sale');
            $this->view->sale = $sale;
            $this->view->saleId = $sale['id'];
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