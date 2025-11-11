<?php

namespace App\Http\Controllers\Api;
use App\Models\Table;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class TableController extends ApiController
{
    
    public function index(): JsonResponse
    {
        return $this->handle(function () {
            return Table::all();
        });
    }

    public function available(): JsonResponse
    {
        return $this->handle(function () {
            return Table::available()->get();
        });
    }
}
