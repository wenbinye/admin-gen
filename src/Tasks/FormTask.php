<?php
namespace AdminGen\Tasks;

use Phalcon\Text;
use Phalcon\Db\Column;
use PhalconX\Cli\Task;
use AdminGen\Inflect;

/**
 * @Task(help="Generate form")
 */
class FormTask extends Task
{
    /**
     * @Argument(required=true, help="The table name for the form")
     */
    public $table;

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
        $vars['namespace'] = 'AdminGen\Forms';
        $vars['class_name'] = $name;
        $vars['columns'] = $this->convertColumns($columns);
        // print_r($columns);
        echo $this->view->getPartial('gen/form', $vars);
    }

    private function convertColumns($columns)
    {
        $ret = [];
        foreach ($columns as $col) {
            $ret[] = [
                'name' => $col->getName(),
                'element' => $this->createElement($col),
                'validator' => $this->createValidator($col)
            ];
        }
        return $ret;
    }

    private function createElement($col)
    {
        
    }

    private function createValidator($col)
    {
        
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
