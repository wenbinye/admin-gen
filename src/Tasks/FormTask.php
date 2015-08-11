<?php
namespace AdminGen\Tasks;

use Phalcon\Text;
use Phalcon\Db\Column;
use PhalconX\Cli\Task;
use AdminGen\Inflect;

/**
 * @Task(help="Generate form")
 */
class FormTask extends ModelTask
{
    /**
     * @Argument(required=true, help="The table name for the form")
     */
    public $table;

    /**
     * @Option('-n', '--namespace', help="The namespace for the model class")
     */
    public $namespace = 'AdminGen\Forms';

    private static $TYPE_ELEMENTS = [
        Column::TYPE_INTEGER => 'Numeric',
        Column::TYPE_DATE => 'Date',
        Column::TYPE_DATETIME => 'Datetime',
        Column::TYPE_BLOB => 'File',
        Column::TYPE_MEDIUMBLOB => 'File',
        Column::TYPE_LONGBLOB => 'File',
        Column::TYPE_TINYBLOB => 'File',
    ];
    
    public function execute()
    {
        $this->gen('gen/form');
    }

    protected function convertColumns($columns)
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
        $type = $col->getType();
        $element = 'Text';
        if ($col->isAutoIncrement()) {
            $element = 'Hidden';
        } elseif ($type == Column::TYPE_VARCHAR || $type == Column::TYPE_CHAR) {
            if ($col->getSize() > 255) {
                $element = 'TextArea';
            }
        } elseif (isset(self::$TYPE_ELEMENTS[$type])) {
            $element = self::$TYPE_ELEMENTS[$type];
        } 
        
        return $element;
    }

    private function createValidator($col)
    {
        $args = [];
        $type = $this->getTypeName($col->getType());
        if ($col->isNotNull() && !$col->isAutoIncrement()) {
            $args['required'] = true;
        }
        if (in_array($type, ['integer', 'string', 'date', 'datetime', 'boolean'])) {
            $args['type'] = $type;
        } elseif (in_array($type, ['decimal', 'float', 'double'])) {
            $args['type'] = 'numeric';
        }
        if ($args['type'] == 'string' && $col->getSize() > 0) {
            $args['maxLength'] = $col->getSize();
        }
        if (!empty($args)) {
            return 'Valid('. $this->stringify($args) .')';
        }
    }
}
