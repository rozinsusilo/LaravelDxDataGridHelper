The main file is `DxDataGridHelper.php` and `EmployeeData.php`. 
You can create your own query repository using `EmployeeData.php` pattern and your own controller like `EmployeeController.php`.
To use the helper simply add `EmployeeData.php` as dependencies on your controller, then create new object from the dependency and finally run render function to generate data as in `EmployeeController.php` file:
```php
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
```
