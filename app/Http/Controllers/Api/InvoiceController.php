<?php

namespace App\Http\Controllers\Api;

use App\Services\InvoiceService;
use App\Http\Controllers\Controller;
use Illuminate\Database\Query\Builder;
use Illuminate\Http\Request;
use Yajra\DataTables\DataTables;

class InvoiceController extends Controller
{
    public function index(DataTables $dataTables, Request $request)
    {
        $service =  new InvoiceService();
        $query = $dataTables->query($service->find())
            ->addIndexColumn()
            ->addColumn('action', function ($invoice) {
                return '<a href="#edit-' . 1 . '" ><i class="material-icons">edit</i></a>';
            });

        return $query->make(true);
    }
}
