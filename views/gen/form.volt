{{ '<?php' }}

namespace {{namespace}};

use PhalconX\Mvc\SimpleModel;

class {{class_name}} extends SimpleModel
{
{% for column in columns %}
   /**
    * @{{ column['element'] }}
    * @{{ column['validator'] }}

    */
   public ${{ column['name'] }};

{% endfor %}
}
