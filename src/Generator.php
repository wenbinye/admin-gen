<?php
namespace AdminGen;

use Phalcon\Text;
use Phalcon\Db\Column;
use Phalcon\Di\Injectable;
use PhalconX\Util;
use PhalconX\Exception;

class Generator
{
    const TYPE_UNKNOWN = 'unknown';

    private static $TYPE_ALIASES = [
        'varchar' => 'string',
        'char' => 'string',
        'text' => 'string',
        'tinyblob' => 'binary',
        'blob' => 'binary',
        'mediumblob' => 'binary',
        'longblob' => 'binary',
    ];

    private static $TYPE_ELEMENTS = [
        Column::TYPE_INTEGER => 'Numeric',
        Column::TYPE_DATE => 'Date',
        Column::TYPE_DATETIME => 'Datetime',
        Column::TYPE_BLOB => 'File',
        Column::TYPE_MEDIUMBLOB => 'File',
        Column::TYPE_LONGBLOB => 'File',
        Column::TYPE_TINYBLOB => 'File',
    ];

    private $table;
    private $tablePrefix;
    private $primaryKey;
    private $namespace;
    private $dir;
    private $viewsDir;
    private $debug;
    private $force;
    private $connectionService;

    private $view;
    private $logger;

    public function __construct($options = null)
    {
        $this->view = Util::service('view', $options);
        $this->logger = Util::service('logger', $options, false);
        if ($options) {
            foreach ($options as $name => $val) {
                $method = 'set' . $name;
                if (method_exists($this, $method)) {
                    $this->$method($val);
                }
            }
        }
    }
    
    public function gen(Type $type)
    {
        $vars = $this->prepare($type);
        $outfile = $this->getFilename($vars['model_name'], $type);
        $dir = dirname($outfile);
        if (!$this->debug && !is_dir($dir) && !mkdir($dir, 0777, true)) {
            die("Cannot create directory $dir");
        }
        if (!$this->debug
            && !$this->force
            && file_exists($outfile)
            && !$this->confirm("Output file $outfile exists, overwrite [y/N] ")) {
            return;
        }
        if (in_array($type->value, [Type::MODEL, Type::FORM])) {
            $columns = $this->getConnection()->describeColumns($this->table);
            $vars['columns'] = $this->getColumns($columns, $type);
        }
        $code = $this->view->getPartial($this->getViewName($type), $vars);
        if ($this->debug) {
            $this->logger->info("Output to file $outfile");
            return $code;
        } else {
            file_put_contents($outfile, $code);
            return $outfile;
        }
    }
    
    protected function prepare(Type $type)
    {
        if (!$this->getConnection()->tableExists($this->table)) {
            throw new Exception("Table {$this->table} does not exist");
        }
        $name = $this->table;
        if ($this->tablePrefix && Text::startsWith($name, $this->tablePrefix)) {
            $name = substr($name, strlen($this->tablePrefix));
            $name = trim($name, '_');
        }
        if (empty($name)) {
            throw new Exception("Cannot infer model name from '{$this->table}'");
        }

        $name = Inflect::singularize($name);
        $model_name = Text::camelize($name);
        $vars['type'] = $type;
        $vars['namespace'] = $this->namespace;
        $vars['model_name'] = $model_name;
        $vars['table_name'] = $this->table;
        $vars['connection'] = $this->connectionService;
        $vars['name'] = str_replace('_', ' ', ucfirst($name));
        $vars['url'] = '/' . str_replace('_', '-', $name);
        $vars['primary_key'] = $this->primaryKey;
        return $vars;
    }

    protected function getColumns($columns, Type $type)
    {
        $ret = [];
        if ($type->value == Type::MODEL) {
            foreach ($columns as $col) {
                $ret[] = [
                    'name' => $col->getName(),
                    'type' => $this->getTypeName($col->getType()),
                    'default' => $this->stringify($col->getDefault())
                ];
            }
        } elseif ($type->value == Type::FORM) {
            foreach ($columns as $col) {
                $ret[] = [
                    'name' => $col->getName(),
                    'element' => $this->createElement($col),
                    'validator' => $this->createValidator($col)
                ];
            }
        }
        return $ret;
    }

    protected function getTypeName($type)
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
        $name = isset($types[$type]) ? $types[$type] : self::TYPE_UNKNOWN;
        return isset(self::$TYPE_ALIASES[$name]) ? self::$TYPE_ALIASES[$name] : $name;
    }

    protected function stringify($value) 
    {
        if (is_bool($value)) {
            return $value ? 'true' : 'false';
        } elseif (is_string($value) && !ctype_alnum($value)) {
            return "'".addslashes($value)."'";
        } elseif (is_array($value)) {
            $args = [];
            foreach ($value as $name => $val) {
                if (is_int($name)) {
                    $args[] = $this->stringify($val);
                } else {
                    $args[] = "$name=" . $this->stringify($val);
                }
            }
            return implode(', ', $args);
        } else {
            return $value;
        }
    }

    protected function getFilename($name, Type $type)
    {
        if ($type->value == Type::VIEW) {
            return Util::catfile($this->viewsDir, Text::uncamelize($name)) . '/index.volt';
        } else {
            $dir = Util::catfile($this->dir, str_replace('\\', '/', $type->namespace));
            $class = ($type->value == Type::CONTROLLER)
                ? $name . 'Controller'
                : $name;
            return Util::catfile($dir, $class.".php");
        }
    }

    protected function confirm($msg)
    {
        if ($this->debug) {
            return true;
        }
        $ans = readline($msg);
        if (in_array(strtolower($ans), ['y', 'yes'])) {
            return true;
        }
    }

    protected function createElement($col)
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

    protected function createValidator($col)
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

    protected function getConnection()
    {
        $conn = $this->connectionService;
        if (!$conn) {
            $conn = 'db';
        }
        return Util::service($conn);
    }

    protected function getViewName(Type $type)
    {
        return 'gen/' . $type->value;
    }
    
    public function getTable()
    {
        return $this->table;
    }

    public function setTable($table)
    {
        $this->table = $table;
        return $this;
    }

    public function getTablePrefix()
    {
        return $this->tablePrefix;
    }

    public function setTablePrefix($tablePrefix)
    {
        $this->tablePrefix = $tablePrefix;
        return $this;
    }

    public function getPrimaryKey()
    {
        return $this->primaryKey;
    }

    public function setPrimaryKey($primaryKey)
    {
        $this->primaryKey = $primaryKey;
        return $this;
    }

    public function getNamespace()
    {
        return $this->namespace;
    }

    public function setNamespace($namespace)
    {
        $this->namespace = $namespace;
        return $this;
    }

    public function getDir()
    {
        return $this->dir;
    }

    public function setDir($dir)
    {
        $this->dir = $dir;
        return $this;
    }

    public function getViewsDir()
    {
        return $this->viewsDir;
    }

    public function setViewsDir($viewsDir)
    {
        $this->viewsDir = $viewsDir;
        return $this;
    }

    public function getDebug()
    {
        return $this->debug;
    }

    public function setDebug($debug)
    {
        $this->debug = $debug;
        return $this;
    }

    public function getForce()
    {
        return $this->force;
    }

    public function setForce($force)
    {
        $this->force = $force;
        return $this;
    }

    public function getConnectionService()
    {
        return $this->connectionService;
    }

    public function setConnectionService($connectionService)
    {
        $this->connectionService = $connectionService;
        return $this;
    }
}
