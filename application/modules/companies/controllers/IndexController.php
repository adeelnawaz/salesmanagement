<?php

class Companies_IndexController extends Zend_Controller_Action {

    public function init() {
        $this->view->selectedTab = 'companies';
    }

    public function indexAction() {
        $this->view->selectedSubTab = 'list_companies';

        $pageRange = $this->_request->getParam('pagerange', 10);
        $pageNo = $this->_request->getParam('page', 1);
        $sort = $this->_request->getParam('sort');
        $order = $this->_request->getParam('order');

        $filter = new Companies_Form_CompanyFilter();
        $filters = array();

        if ($this->_request->isPost() && $filter->isValid($this->_request->getPost())) {
            $filters = $filter->getValues();
            $this->view->searched = TRUE;
        }

        $companyModel = new Application_Model_DbTable_Companies();

        //Paginator...
        $select = $companyModel->getCompaniesPaginatedQuery($filters, $sort, $order);
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
        $this->view->selectedSubTab = 'add_company';
        $this->view->placeholder('heading')->set($this->view->translate('Add company'));

        $companiesModel = new Application_Model_DbTable_Companies();

        $form = new Companies_Form_Company();

        //Changes in form
        $form->removeElement('status');

        if ($this->_request->isPost() && $form->isValid($this->_request->getPost())) {
            $data = $form->getValues();
            $companiesModel->save($data);

            $this->_redirect($this->view->url(array('module' => 'companies'), NULL, TRUE));
        }

        $this->view->form = $form;
    }

    public function editAction() {
        $this->view->placeholder('heading')->set($this->view->translate('Edit company'));

        $companyId = $this->_request->getParam('id');

        $companyModel = new Application_Model_DbTable_Companies();
        $company = $companyModel->getCompanyArray($companyId);

        $form = new Companies_Form_Company();
        $form->populate($company);

        if ($this->_request->isPost() && $form->isValid($this->_request->getPost())) {
            $data = $form->getValues();
            $data['id'] = $company['id'];
            $companyModel->save($data);

            $this->_redirect($this->view->url(array('module' => 'companies'), NULL, TRUE));
        }

        $this->view->form = $form;

        $this->render('add');
    }

    public function deleteAction() {
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender(TRUE);

        $companyId = $this->_request->getParam('id');

        if ($this->_request->isPost()) {
            $deleteModels = $this->_request->getPost('delete_models');

            $companiesModel = new Application_Model_DbTable_Companies();

            $companiesModel->deleteCompany($companyId);

            $productModelsModel = new Application_Model_DbTable_ProductModels();
            if ($deleteModels == 'on') {
                $productModelsModel->deleteProductModelsByCompanyId($companyId);
            } else {//Mark models without a company
                $productModelsModel->updateProductModelsByCompanyId(array('company_id' => -1), $companyId);
            }
        }

        $this->_redirect($this->view->url(array('module' => 'companies'), NULL, TRUE));
    }

    function autocompleteAction() {
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender(TRUE);

        $term = $this->_request->getParam('term');

        $companiesModel = new Application_Model_DbTable_Companies();

        $companies = $companiesModel->getAutoComplete($term);

        echo json_encode($companies);
    }

}

