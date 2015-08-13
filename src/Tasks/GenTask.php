<?php
namespace AdminGen\Tasks;

use PhalconX\Di\Injectable;
use PhalconX\Mvc\SimpleModel;
use AdminGen\Type;

/**
 * @TaskGroup(gen, help="Generate crud model, form, controller etc.")
 */
class GenTask extends SimpleModel
{
    use Injectable;
    
    /**
     * @Argument(required=true, help="The table name for the model")
     */
    public $table;

    /**
     * @Option('--primary-key', help="primary key column name")
     */
    public $primaryKey = 'id';

    /**
     * @Option('-p', '--prefix', help="The table prefix")
     */
    public $prefix;

    /**
     * @Option('-n', '--namespace', help="The namespace for the model class")
     */
    public $namespace = 'AdminGen';

    /**
     * @Option('-d', '--dir', help="Output directory")
     */
    public $dir = 'src';

    /**
     * @Option('--views-dir', help="Views directory")
     */
    public $viewsDir = 'views';

    /**
     * @Option('-c', '--connection', help="Database connection service name")
     */
    public $connection;

    /**
     * @Option('--debug', type=boolean, help="turn on debug mode")
     */
    public $debug;

    /**
     * @Option('-f', '--force', type=boolean, help="Overwrite exist file without prompt")
     */
    public $force;

    private function getGenerator()
    {
        return $this->generator->setTable($this->table)
            ->setTablePrefix($this->prefix)
            ->setNamespace($this->namespace)
            ->setDir($this->dir)
            ->setConnectionService($this->connection)
            ->setPrimaryKey($this->primaryKey)
            ->setViewsDir($this->viewsDir)
            ->setForce($this->force)
            ->setDebug($this->debug);
    }
    
    /**
     * @Task(help="Generate model")
     */
    public function modelAction()
    {
        $this->gen(Type::MODEL());
    }

    /**
     * @Task(help="Generate form")
     */
    public function formAction()
    {
        $this->gen(Type::FORM());
    }

    /**
     * @Task(help="Generate form")
     */
    public function controllerAction()
    {
        $this->gen(Type::CONTROLLER());
    }

    /**
     * @Task(help="Generate view")
     */
    public function viewAction()
    {
        $this->gen(Type::VIEW());
    }

    private function gen(Type $type)
    {
        $out = $this->getGenerator()->gen($type);
        if ($this->debug) {
            echo $out;
        } else {
            echo "Code was generated to $out\n";
        }
    }
    
    /**
     * @Task(help="Generate all")
     */
    public function allAction()
    {
        $this->modelAction();
        $this->formAction();
        $this->controllerAction();
        $this->viewAction();
    }
}
