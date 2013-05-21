<?php

class Productmodels_IndexController extends Zend_Controller_Action {

    public function init() {
        $this->view->selectedTab = 'productmodels';
    }

    public function indexAction() {
        $this->view->selectedSubTab = 'list_productmodels';

        $this->view->placeholder('heading')->set($this->view->translate("Product models"));

        $companyId = $this->_request->getParam('id');

        $pageRange = $this->_request->getParam('pagerange', 10);
        $pageNo = $this->_request->getParam('page', 1);
        $sort = $this->_request->getParam('sort');
        $order = $this->_request->getParam('order');

        $filter = new Productmodels_Form_ProductmodelFilter();
        $filters = array();

        $productmodelsModel = new Application_Model_DbTable_ProductModels();

        if ($this->_request->isPost() && $filter->isValid($this->_request->getPost())) {
            $filters = $filter->getValues();
            $this->view->searched = TRUE;
        }

        if (!empty($companyId)) {
            $companiesModel = new Application_Model_DbTable_Companies();
            $company = $companiesModel->getCompany($companyId);
            $compayName = $company->name;

            $this->view->placeholder('heading')->set($this->view->translate("%s's product models", $compayName));

            $filters['company_id'] = $companyId;
            //$productmodelsStats = $productmodelsModel->getProductmodelsStatsByCustomerId($customerId);
            $this->view->companyProductmodels = TRUE;
            $this->view->company = $company;
            $this->view->companyId = $companyId;
            //$this->view->productmodelsStats = $productmodelsStats;
        }

        //Paginator...
        $select = $productmodelsModel->getProductmodelsPaginatedQuery($filters, $sort, $order);
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
        $companyId = $this->_request->getParam('id');

        $this->view->selectedSubTab = 'add_productmodel';
        $this->view->placeholder('heading')->set($this->view->translate('Add product model'));

        $productmodelsModel = new Application_Model_DbTable_ProductModels();

        $companyModel = new Application_Model_DbTable_Companies();

        $companies = $companyModel->getCompanies();
        if (count($companies) == 0) {
            $errorMessages = array($this->view->translate('Please add a company first'));
            $this->view->errorMessages = $errorMessages;
            return;
        }

        $form = new Productmodels_Form_Productmodel();
        $form->removeElement('status');

        foreach ($companies as $company) {
            $form->company_id->addMultiOption($company->id, $company->name);
        }

        if (!empty($companyId)) {
            $companyModel = new Application_Model_DbTable_Companies();
            $company = $companyModel->getCompany($companyId);
            $companyName = $company->name;

            $form->company_id->setValue($company->id);

            $this->view->companyName = $companyName;
            $this->view->companyProductmodels = TRUE;
        }

        if ($this->_request->isPost() && $form->isValid($this->_request->getPost())) {
            $data = $form->getValues();
            $data['created_at'] = Zend_Date::now()->toString('yyyy-MM-dd HH:mm:ss');

            $productmodelsModel->save($data);

            $this->_redirect($this->view->url(array('module' => 'productmodels', 'id' => (!empty($companyId) ? $companyId : NULL)), NULL, TRUE));
        }

        $this->view->form = $form;
        $this->view->companies = $companies;
    }

    public function editAction() {
        $this->view->placeholder('heading')->set($this->view->translate('Edit product model'));

        $productmodelId = $this->_request->getParam('id');

        $productModelsModel = new Application_Model_DbTable_ProductModels();
        $productmodel = $productModelsModel->getProductModelArray($productmodelId);

        $companyModel = new Application_Model_DbTable_Companies();

        $companies = $companyModel->getCompanies();

        $form = new Productmodels_Form_Productmodel();

        if (count($companies)) {
            foreach ($companies as $company) {
                $form->company_id->addMultiOption($company->id, $company->name);
            }
        }
        if ($productmodel['company_id'] == -1) {
            $form->company_id->addMultiOption(-1, $this->view->translate('No company'));
        }

        $form->populate($productmodel);

        if ($this->_request->isPost() && $form->isValid($this->_request->getPost())) {
            $data = $form->getValues();
            $data['id'] = $productmodel['id'];
            $productModelsModel->save($data);

            $this->_redirect($this->view->url(array('module' => 'productmodels'), NULL, TRUE));
        }

        $this->view->form = $form;

        $this->render('add');
    }
    
    public function deleteAction() {
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender(TRUE);

        $productmodelId = $this->_request->getParam('id');

        $companyModel = new Application_Model_DbTable_Companies();
        $company = $companyModel->getCompanyByProductModelId($productmodelId);

        if (!isset($company['id'])) {
            $productModelsModel = new Application_Model_DbTable_ProductModels();
            $productModelsModel->deleteProductModel($productmodelId);
        } else {
            $message = str_replace(array('[', ']'), array('<a href="' . $this->view->url(array('module' => 'companies'), NULL, TRUE) . '">', '</a>'), $this->view->translate('Please delete the [company] first'));
            $this->view->addAlertMessage($message, 'error');
        }

        $this->_redirect($this->view->url(array('module' => 'productmodels'), NULL, TRUE));
    }
    
    public function bulkdeleteAction() {
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender(TRUE);

        $productmodelIds = $this->_request->getParam('id');
        $productmodelIds = explode(',', $productmodelIds);

        $companyModel = new Application_Model_DbTable_Companies();
        $companies = $companyModel->getCompaniesByProductModelIds($productmodelIds);

        if (count($companies) == 0) {
            $productModelsModel = new Application_Model_DbTable_ProductModels();
            $productModelsModel->deleteProductModels($productmodelIds);
        } else {
            $message = $this->view->translate('Please delete the companies first');
            $this->view->addAlertMessage($message, 'error');
        }

        $this->_redirect($this->view->url(array('module' => 'productmodels'), NULL, TRUE));
    }

}

