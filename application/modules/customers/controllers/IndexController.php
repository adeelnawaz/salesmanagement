<?php

class Customers_IndexController extends Zend_Controller_Action {

    public function init() {
        $this->view->selectedTab = 'customers';
    }

    public function indexAction() {
        $this->view->selectedSubTab = 'list_customers';

        $pageRange = $this->_request->getParam('pagerange', 10);
        $pageNo = $this->_request->getParam('page', 1);
        $sort = $this->_request->getParam('sort');
        $order = $this->_request->getParam('order');

        $filter = new Customers_Form_CustomerFilter();
        $filters = array();

        if ($this->_request->isPost() && $filter->isValid($this->_request->getPost())) {
            $filters = $filter->getValues();
            $this->view->searched = TRUE;
        }

        $customerModel = new Application_Model_DbTable_Customers();

        //Paginator...
        $select = $customerModel->getCustomerPaginatedQuery($filters, $sort, $order);
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
        $this->view->selectedSubTab = 'add_customer';
        $this->view->placeholder('heading')->set($this->view->translate('Add customer'));

        $customerModel = new Application_Model_DbTable_Customers();

        $form = new Customers_Form_Customer();

        //Changes in form
        $form->removeElement('status');

        if ($this->_request->isPost() && $form->isValid($this->_request->getPost())) {
            $data = $form->getValues();
            $customerModel->save($data);

            $this->_redirect($this->view->url(array('module' => 'customers'), NULL, TRUE));
        }

        $this->view->form = $form;
    }

    public function editAction() {
        $this->view->placeholder('heading')->set($this->view->translate('Edit customer'));

        $customerId = $this->_request->getParam('id');

        $customerModel = new Application_Model_DbTable_Customers();
        $customer = $customerModel->getCustomerArray($customerId);

        $form = new Customers_Form_Customer();
        $form->populate($customer);

        if ($this->_request->isPost() && $form->isValid($this->_request->getPost())) {
            $data = $form->getValues();
            $data['id'] = $customer['id'];
            $customerModel->save($data);

            $this->_redirect($this->view->url(array('module' => 'customers'), NULL, TRUE));
        }

        $this->view->form = $form;

        $this->render('add');
    }

    public function clearhistoryAction() {
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender(TRUE);

        $customerId = $this->_request->getParam('id');

        if ($this->_request->isPost()) {
            $deleteProducts = $this->_request->getPost('delete_products');

            $salesModel = new Application_Model_DbTable_Sales();

            $saleIds = $salesModel->getSaleIdsByCustomerId($customerId);

            if (!empty($saleIds)) {
                $salesModel->deleteSalesByIds($saleIds);

                if ($deleteProducts == 'on') {
                    $productsModel = new Application_Model_DbTable_Products();
                    $productsModel->deleteProductsBySaleIds($saleIds);
                }
            }
        }

        $this->_redirect($this->view->url(array('module' => 'customers', 'action' => 'edit', 'id' => $customerId), NULL, TRUE));
    }

    public function deleteAction() {
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender(TRUE);

        $customerId = $this->_request->getParam('id');

        if ($this->_request->isPost()) {
            $deleteSales = $this->_request->getPost('delete_sales');
            $deleteProducts = $this->_request->getPost('delete_products');

            $customersModel = new Application_Model_DbTable_Customers();

            $customersModel->deleteCustomer($customerId);

            if ($deleteSales == 'on') {
                $salesModel = new Application_Model_DbTable_Sales();

                $saleIds = $salesModel->getSaleIdsByCustomerId($customerId);

                if (!empty($saleIds)) {
                    $salesModel->deleteSalesByIds($saleIds);

                    if ($deleteProducts == 'on') {
                        $productsModel = new Application_Model_DbTable_Products();
                        $productsModel->deleteProductsBySaleIds($saleIds);
                    }
                }
            }
        }

        $this->_redirect($this->view->url(array('module' => 'customers'), NULL, TRUE));
    }

    function autocompleteAction() {
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender(TRUE);

        $term = $this->_request->getParam('term');

        $customerModel = new Application_Model_DbTable_Customers();

        $customers = $customerModel->getAutoComplete($term);

        /*$return = array();
        foreach ($customers as $customer) {
            $return[$customer['id']] = $customer['customer_name'];
        }*/

        //echo json_encode($return);
        echo json_encode($customers);
    }

}

