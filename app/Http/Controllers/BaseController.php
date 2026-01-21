<?php

namespace App\Http\Controllers;

use App\Traits\ApiResponseTrait;

abstract class BaseController extends Controller
{
    use ApiResponseTrait;
}
