<?php
namespace AdminGen\Forms\DataTables;

class Order
{
    /**
     * @Valid(type=integer)
     */
    public $column;

    /**
     * @Valid(type=string, enum=[asc, desc])
     */
    public $dir;
}
