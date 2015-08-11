<?php
namespace AdminGen\Forms\DataTables;

class Query
{
    /**
     * @Valid(type=integer, default=1)
     */
    public $draw;
    
    /**
     * @Valid(type=integer, default=0)
     */
    public $start;

    /**
     * @Valid(type=integer, default=10, maximum=100, minimum=10)
     */
    public $length;

    /**
     * @IsA(Search)
     */
    public $search;

    /**
     * @IsA("Order[]")
     */
    public $order;

    /**
     * @IsA("Column[]")
     */
    public $columns;
}
