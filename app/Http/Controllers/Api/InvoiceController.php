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
        $user = $request->user();
        $user = (!$user->is_admin)? $user : null;

        $customerId = null;
        if($request->filled('columns.1.search.value')) {
            $customerId = $request->input('columns.1.search.value');

            $dataRequest = $request->all();
            $dataRequest['columns'][1]['search']['value'] = '';
            $request->merge($dataRequest);
        }

        $query = $dataTables->query($service->find($user))
            ->addIndexColumn()
            ->filter(function (Builder $query) use ($customerId) {
                if($customerId) {
                    $query->where("customer_id", $customerId);
                }
            }, true)
            ->addColumn('action', function ($invoice) {
                return '<a data-toggle="modal" data-target="#modalInvoice" href="#modalInvoice" data-remote="' . route('invoice.show', ['invoice' => $invoice->invoice_code ]) . '" >' .  __('Show Invoice').'</a>'
                    . ((!in_array($invoice->status, ['Cancelada', 'Pago']) )? ' | <a target="_blank" href="' . route('invoice.billet', ['invoice' => $invoice->invoice_code ]) . '" >' .  __('Billet').'</a>' : '')
                    . ' | <a class="invoice-print" target="_blank" href="' . route('invoice.show', ['invoice' => $invoice->invoice_code ]) . '?print=true" >' .  __('Print').'</a>'
                    . ' | <a href="' . route('invoice.csv', ['invoice' => $invoice->invoice_code ]) . '" >' .  __('CSV').'</a>';
            });

        return $query->make(true);
    }
}
