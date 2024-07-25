<?php

namespace App\Http\Controllers;

use App\EmployeeData;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class LicommController extends Controller
{
    
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function data(Request $request)
    {
        $employeeData = new EmployeeData();
        
        return $employeeData->render();
    }
