<?php

namespace App\Helpers;

class DxDataGridHelper {

    protected $query;
    protected $args;
    protected $filterable;
    protected $sortable;
    protected $defaultSort;

    function render() {
        $this->args = request()->input();

        if(isset($this->args['filter'])) {
            $this->query = $this->addFilter();
        }

        if(isset($this->args['group'])) {
            return $this->addGroup();
        }

        $this->query = $this->addSort();

        if(isset($this->args['take'])) {
            $result = $this->query->paginate($this->args['take']);
            $items = $result->items();
            $total = $result->total();
        } else {
            $result = $this->query->get();
            $items = $result;
            $total = $result->count();
        }

        return response()->json([
            'data' => $items,
            'totalCount' => $total,
        ]);
    }

    function addFilter() {
        $filter = json_decode($this->args['filter'], true);
        $convertFilter = $this->convertFilter($filter, $this->filterable);

        return $this->query->whereRaw($convertFilter);
    }

    function addGroup() {
        $columnDate = ['closed_at', 'created_at', 'updated_at', 'register_at'];
        $groupDate = ['year', 'month', 'day'];

        $group = json_decode($this->args['group'], true);
        
        $columnName = $group[0]['selector'];
        
        if(in_array($columnName, $columnDate)) {
            return $this->dateFilter($columnName);
        }
        $this->query = $this->query->groupBy($columnName)->select($columnName." as key")->get()->toArray();
        
        return response()->json(['data' => $this->query]);
    }

    function addSort() {
        if (isset($this->args['sort'])) {
            $this->defaultSort = json_decode($this->args['sort'], true);
        }
        $sortQuery = array_map(function ($d) {
            return $d['selector'].' '.($d['desc'] ? 'DESC' : 'ASC');
        }, $this->defaultSort);
        $sortQuery = implode(', ', $sortQuery);
        return $this->query->orderByRaw($sortQuery);
    }

    private function dateFilter($columnName) {
        $query = $this->query;
        $oldest = $query->orderBy($columnName,'asc')->where($columnName, '<>', NULL)->first();
        $oldest = intval(date('Y', strtotime($oldest[$columnName]))); 
        $newest = intval(date('Y'));

        $filterDate = [];
        for($y = $oldest; $y <= $newest; $y++) {
            $result = ['key' => $y, 'items' => []];
            $months = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
            for ($m = 0; $m < 12; $m++) {
                $resMonth = ['key'=> $m+1, 'text' => $months[$m], 'items' => []];
                array_push($result['items'], $resMonth);                        
            }
            array_push($filterDate, $result);
        }
        
        return ['data' => $filterDate];
    }
    
    private function convertFilter($filter, $column) : String {
        $notation = ['!', '<', '<=', '=', '>', '>=', 'and', 'or'];
        
        $convert = function($item) use (&$convert, $column, $notation) {
            if(is_string($item) || is_numeric($item)) {
                if(!in_array($item, $notation) && !in_array($item, $column)) {
                    return is_string($item) ? "'$item'" : $item;
                }
                return $item;
            } else {
                if(is_array($item)) {
                    if(is_array($item[0])) {
                        $maps = array_map($convert, $item);
                        return "(".implode(" ", $maps).")";  
                    } else {
                        if(count($item) == 3) {
                            
                            return $this->toSqlArray($item);

                        } else {
                            $maps = array_map($convert, $item);
                            return "(".implode(" ", $maps).")";
                        }
                    }
                } 
            }
        };

        if(!$this->checkIsHaveArray($filter)) {
            $result = $this->toSqlArray($filter);
        } else {
            $convertFilter = array_map($convert, $filter);
            $result = "(".implode(" ", $convertFilter).")";
        }        

        // dd($filter, $convertFilter ?? null, $result);

        return $result;
    }

    function toSqlArray($item) {

        if(isset($item[1]) && $item[1] == "contains") {
            $item[1] = "LIKE";
            $item[2] = "'%$item[2]%'";
        } else {
            if(isset($item[2])) { 
                if(is_string($item[2])) {
                    if(empty($item[2]) || strlen($item[2]) == 0 || is_null($item[2])) {
                        return "$item[0] IS NULL";  
                    } else {
                        $item[2] = "'$item[2]'";
                    }
                }  
                
            } else {
                return "$item[0] IS NULL";  
            }
        }
        return implode(" ", $item);
    }

    function checkIsHaveArray($value) {
        $result = [];
        array_walk($value, function($v, $k) use (&$result) {
            $result[] = is_array($v);
        });

        return in_array(true, $result);
    }

}