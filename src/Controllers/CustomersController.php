<?php
namespace AdminGen\Controllers;

use AdminGen\Forms\Customer as CustomerForm;
use AdminGen\Model\Customer;

class CustomerController extends ControllerBase
{
    public function indexAction()
    {
        $form = $this->validator->createForm(CustomerForm::CLASS);
        $this->render([
            'columns' => $form->getColumns(),
        ]);
    }

    public function createAction()
    {
    }

    public function editAction()
    {
    }

    public function deleteAction()
    {
    }

    /**
     * @Json
     */
    public function listAction()
    {
    }
}
