{{ '<?php' }}

namespace {{namespace}};

use PhalconX\Mvc\Model;

class {{class_name}} extends Model
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
}
