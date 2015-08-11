<?php
namespace AdminGen\Tasks;

use Phalcon\Text;
use Phalcon\Db\Column;
use PhalconX\Cli\Task;
use AdminGen\Inflect;

/**
 * @Task(help="Generate model")
 */
class ModelTask extends Task
{
    /**
     * @Argument(required=true, help="The table name for the model")
     */
    public $table;

    /**
     * @Option('-n', '--namespace', help="The namespace for the model class")
     */
    public $namespace = 'AdminGen\Controllers';

    private static $TYPE_ALIASES = [
        'varchar' => 'string',
        'char' => 'string',
        'text' => 'string',
        'tinyblob' => 'binary',
        'blob' => 'binary',
        'mediumblob' => 'binary',
        'longblob' => 'binary',
    ];
    
    public function execute()
    {
        $db = $this->db;
        $columns = $db->describeColumns($this->table);
        $name = Text::camelize(Inflect::singularize($this->table));
        $vars['namespace'] = $this->namespace;
        $vars['class_name'] = $name;
        $vars['table_name'] = $this->table;
        $vars['columns'] = $this->convertColumns($columns);
        // print_r($columns);
        echo $this->view->getPartial('gen/model', $vars);
    }

    private function convertColumns($columns)
    {
        $ret = [];
        foreach ($columns as $col) {
            $ret[] = [
                'name' => $col->getName(),
                'type' => $this->getTypeName($col->getType()),
                'default' => $this->stringify($col->getDefault())
            ];
        }
        return $ret;
    }

    private function getTypeName($type)
    {
        static $types;
        if (!$types) {
            $refl = new \ReflectionClass(Column::CLASS);
            foreach($refl->getConstants() as $name => $val) {
                if (Text::startsWith($name, 'TYPE_')) {
                    $types[$val] = strtolower(substr($name, 5));
                }
            }
        }
        $name = isset($types[$type]) ? $types[$type] : 'unknown';
        return isset(self::$TYPE_ALIASES[$name]) ? self::$TYPE_ALIASES[$name] : $name;
    }

    private function stringify($value) 
    {
        if (is_string($value)) {
            return "'".addslashes($value)."'";
        } else {
            return $value;
        }
    }
}
