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
    const TYPE_UNKNOWN = 'unknown';
    
    /**
     * @Argument(required=true, help="The table name for the model")
     */
    public $table;

    /**
     * @Option('-n', '--namespace', help="The namespace for the model class")
     */
    public $namespace = 'AdminGen\Models';

    /**
     * @Option('-d', '--dir', help="Output directory")
     */
    public $dir = 'src';

    /**
     * @Option('-p', '--prefix', help="autoload prefix, affetct the output directory")
     */
    public $prefix = 'AdminGen';

    /**
     * @Option('--debug', type=boolean, help="turn on debug mode")
     */
    public $debug;

    protected static $TYPE_ALIASES = [
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
        $this->gen('gen/model');
    }

    protected function gen($view)
    {
        $name = Text::camelize(Inflect::singularize($this->table));
        $outfile = $this->getFilename($name);
        if (file_exists($outfile)
            && !$this->confirm("Output file $outfile exists, overwrite [y/N] ")) {
            return;
        }

        $columns = $this->db->describeColumns($this->table);
        $vars['namespace'] = $this->namespace;
        $vars['class_name'] = $name;
        $vars['table_name'] = $this->table;
        $vars['columns'] = $this->convertColumns($columns);
        // print_r($columns);
        $code = $this->view->getPartial($view, $vars);
        if ($this->debug) {
            echo $code;
        } else {
            file_put_contents($outfile, $code);
            echo "Code was generated to $outfile\n";
        }
    }

    protected function convertColumns($columns)
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

    protected function getFilename($name)
    {
        if ($this->prefix) {
            if (!Text::startsWith($this->namespace, $this->prefix)) {
                die("namespace '{$this->namespace}' should start with '{$this->prefix}'");
            }
            $ns = substr($this->namespace, strlen($this->prefix));
        } else {
            $ns = $this->namespace;
        }
        
        $dir = ($this->dir ? rtrim($this->dir, '/') . '/' : '')
            . str_replace('\\', '/', trim($ns, '\\'));
        if (!$this->debug && !is_dir($dir) && !mkdir($dir, 0777, true)) {
            die("Cannot create directory $dir");
        }
        return rtrim($dir, '/') . "/$name.php";
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
}
