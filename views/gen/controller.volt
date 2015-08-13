{{ '<?php' }}

namespace {{ namespace }}\{{ type.namespace }};

use {{ namespace }}\Forms\{{ model_name }} as {{ model_name }}Form;
use {{ namespace }}\Models\{{ model_name }};
use AdminGen\FormHelper;
use AdminGen\Forms\DataTables\Query;
use PhalconX\Exception;
use PhalconX\Exception\ValidationException;
use AdminGen\Annotations\Sidebar;

/**
 * @Sidebar(url="{{ url }}", label="{{ name }}")
 * @RoutePrefix("{{ url }}")
 * @Route(":action/:params", paths=[action=1,params=2])
 * @Route(":action", paths=[action=1])
 */
class {{ model_name }}Controller extends ControllerBase
{
    public function indexAction()
    {
        $form = $this->validator->createForm({{ model_name }}Form::CLASS);
        FormHelper::process($form);
        $this->render([
            'columns' => FormHelper::getColumns($form),
            'has_textarea' => FormHelper::hasTextarea($form),
            'form' => $form,
            'primary_key' => '{{ primary_key }}'
        ]);
    }

    /**
     * @Json
     * @nCsrfToken(repeatOk=true)
     */
    public function createAction()
    {
        $this->save(new {{ model_name }});
    }

    /**
     * @Json
     * @CsrfToken(repeatOk=true)
     */
    public function updateAction()
    {
        $id = $this->request->get('id');
        $model = {{ model_name }}::findFirst($id);
        if (!$model) {
            throw new Exception(
                "{{ model_name }} id '$id' not found",
                Exception::ERROR_NOT_FOUND
            );
        }
        $this->save($model);
    }

    private function save($model)
    {
        $form = $this->validator->createForm({{ model_name }}Form::CLASS);
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
        $model = {{ model_name }}::findFirst($id);
        if (!$model) {
            throw new Exception(
                "{{ model_name }} id '$id' not found",
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
        $count = {{ model_name }}::count($criteria);
        $criteria['limit'] = [
            'offset' => $query->start,
            'number' => $query->length
        ];
        $models = {{ model_name }}::find($criteria)->toArray();
        $this->response->setJsonContent(array(
            'draw' => $query->draw,
            'start' => $query->start,
            'data' => $models,
            'recordsTotal' => $count,
            'recordsFiltered' => $count
        ));
    }
}
