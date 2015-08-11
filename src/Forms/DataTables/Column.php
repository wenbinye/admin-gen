<?php
namespace AdminGen\Models\DataTables;

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
