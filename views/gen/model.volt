{{ '<?php' }}

namespace {{ namespace }}\{{ type.namespace }};

use PhalconX\Mvc\Model;

class {{ model_name }} extends Model
{
{% for column in columns %}
   /**
    * @var {{ column['type'] }}

    */
   public ${{ column['name'] }}{% if column.default is defined %} = {{ column.default }}{% endif %};

{% endfor %}
   public function getSource()
   {
       return "{{ table_name }}";
   }
{% if connection is defined %}

   public function initialize()
   {
       $this->setConnectionService('{{ connection }}');
   }
{% endif %}
}
