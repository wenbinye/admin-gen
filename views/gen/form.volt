{{ '<?php' }}

namespace {{namespace}}\{{ type.namespace }};

use PhalconX\Mvc\SimpleModel;

class {{ model_name }} extends SimpleModel
{
{% for column in columns %}
   /**
    * @{{ column['element'] }}

{% if column['validator'] is defined %}
    * @{{ column['validator'] }}

{% endif %}
    */
   public ${{ column['name'] }};
{% if not loop.last %}

{% endif %}
{% endfor %}
}
