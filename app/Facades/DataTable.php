<?php

namespace App\Facades;

use Illuminate\Support\Facades\Facade;
use App\Services\DataTableService;

class DataTable extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'datatable';
    }

    /**
     * Factory method to create a new instance of DataTableService.
     *
     * @param string $table
     * @param array  $columns
     * @param string $orderField
     * @param string $orderDirection
     * @param array  $searchFields
     * @param array  $whereConditions
     * @param array  $joins
     * @return \App\Services\DataTableService
     */
    public static function make($table, $columns, $searchFields = [], $whereConditions = [], $joins = [])
    {
        return new DataTableService($table, $columns, $searchFields, $whereConditions, $joins);
    }
}
