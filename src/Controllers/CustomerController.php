<?php
namespace AdminGen\Controllers;

use AdminGen\Forms\Customer as CustomerForm;
use AdminGen\Models\Customer;
use AdminGen\FormHelper;
use AdminGen\Forms\DataTables\Query;
use PhalconX\Exception;
use PhalconX\Exception\ValidationException;

/**
 * @RoutePrefix("/customer")
 * @Route(":action/:params", paths=[action=1,params=2])
 * @Route(":action", paths=[action=1])
 */
class CustomerController extends ControllerBase
{
    public function indexAction()
    {
        $form = $this->validator->createForm(CustomerForm::CLASS);
        FormHelper::process($form);
        $this->render([
            'columns' => FormHelper::getColumns($form),
            'has_textarea' => FormHelper::hasTextarea($form),
            'form' => $form,
            'primary_key' => 'id'
        ]);
    }

    /**
     * @Json
     * @nCsrfToken(repeatOk=true)
     */
    public function createAction()
    {
        $this->save(new Customer);
    }

    /**
     * @Json
     * @CsrfToken(repeatOk=true)
     */
    public function updateAction()
    {
        $id = $this->request->get('id');
        $model = Customer::findFirst($id);
        if (!$model) {
            throw new Exception(
                "Customer id '$id' not found",
                Exception::ERROR_NOT_FOUND
            );
        }
        $this->save($model);
    }

    private function save($model)
    {
        $form = $this->validator->createForm(CustomerForm::CLASS);
        if ($form->isValid($this->request->getPost(), $model)) {
            if ($model->save()) {
                $this->response->setJsonContent([
                    'success' => true,
                    'id' => $model->id
                ]);
            } else {
                throw new ValidationException($model->getMessages());
            }
        } else {
            throw new ValidationException($form->getMessages());
        }
    }
    
    /**
     * @Json
     * @CsrfToken(repeatOk=true)
     */
    public function deleteAction($id)
    {
        $model = Customer::findFirst($id);
        if (!$model) {
            throw new Exception(
                "Customer id '$id' not found",
                Exception::ERROR_NOT_FOUND
            );
        }
        if ($model->delete()) {
            $this->response->setJsonContent([
                'success' => true,
                'id' => $model->id
            ]);
        } else {
            throw new ValidationException($model->getMessages());
        }
    }

    /**
     * @Json
     */
    public function listAction()
    {
        $query = $this->objectMapper->map($this->request->get(), Query::CLASS);
        $criteria = [];
        $count = Customer::count($criteria);
        $criteria['limit'] = [
            'offset' => $query->start,
            'number' => $query->length
        ];
        $models = Customer::find($criteria)->toArray();
        $this->response->setJsonContent(array(
            'draw' => $query->draw,
            'start' => $query->start,
            'data' => $models,
            'recordsTotal' => $count,
            'recordsFiltered' => $count
        ));
    }
}
