<?php
namespace AdminGen\Forms\DataTables;

class Column
{
    public $data;

    public $name;

    /**
     * @Valid(type=boolean)
     */
    public $searchable;

    /**
     * @Valid(type=boolean)
     */
    public $orderable;

    /**
     * @IsA(Search)
     */
    public $search;
}
