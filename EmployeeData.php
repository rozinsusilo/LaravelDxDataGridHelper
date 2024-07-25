<?php

namespace App;

use App\DxDataGridHelper;
use App\Models\Employee;
use DB;

class EmployeeData extends DxDataGridHelper {

    protected $defaultFilter;

    function __construct() {
        $this->defaultFilter = $this->setDefaultFilter();
        $this->defaultSort = $this->setDefaultSort();
        $this->filterable = $this->setFilterable();
        $this->sortable = $this->setSortable();
        $this->query = $this->setQuery();
    }

    function setQuery() {
        $cityQuery = "(select id, name as city_name from city) as c";
        $managerQuery = "(select u.name as manager_name, d.name as distributor_name from managers u, distributors d 
                        where d.id = u.distributor_id AND deleted_at IS NULL) as manager_data";
        
        $query = Employee::where($this->defaultFilter)
                              ->join(DB::raw($managerQuery), 'u.id', 'employee.manager_id')
                              ->join(DB::raw($cityQuery), 'employee.city_id', 'c.id')
                              ->select('employee.id', 'employee.name', 'email', 'phone_number', 'register_at', 'employee.is_active', 'employee.deleted_at',
                                        'manager_data.distributor_name', 'manager_data.manager_name',
                                        'c.city_name'
                                );
        return $query;
    }

    function setDefaultFilter() {
        return ['is_active' => 0, 'deleted_at' => NULL];
    }

    function setDefaultSort() {
        return [['selector' => 'register_at', 'desc' => true]];
    }

    function setFilterable() {
        return ['name', 'email', 'phone_number', 'manager_name', 'distributor_name', 'city_name', 'is_active', 'register_at'];
    }

    function setSortable() {
        return ['name', 'email', 'manager_name', 'city_name', 'is_active', 'register_at', 'distributor_name'];
    }

}
