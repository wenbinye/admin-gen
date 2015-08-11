<?php
namespace AdminGen\Tasks;

use PhalconX\Cli\Task;

/**
 * @Task(help="Generate all classes")
 */
class GenTask extends Task
{
    /**
     * @Argument(required=true, help="The table name")
     */
    public $table;

    /**
     * @Option('-d', '--dir', help="Output directory")
     */
    public $dir = 'src';

    /**
     * @Option('-p', '--prefix', help="autoload prefix, affetct the output directory")
     */
    public $prefix = 'AdminGen';

    public function execute()
    {
        $di = $this->getDi();
        $prefix = trim($this->prefix, '\\');
        $di->get(ModelTask::CLASS, [[
            'table' => $this->table,
            'dir' => $this->dir,
            'prefix' => $prefix,
            'namespace' => $prefix . '\\Models'
        ]])->execute();
        $di->get(FormTask::CLASS, [[
            'table' => $this->table,
            'dir' => $this->dir,
            'prefix' => $prefix,
            'namespace' => $prefix . '\\Forms'
        ]])->execute();
    }
}
