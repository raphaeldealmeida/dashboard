<?php

namespace App\Http\Controllers;

use App\Company;
use App\Customer;
use App\CustomerLegacy;
use App\Http\Requests\CustomerRequest;
use App\Http\Requests\CustomerUserRequest;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class CustomerController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return view('customers.index');
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create(Request $request)
    {
        $code = (int) $request->get('code');
        if(!$code)
            return view('customers.code');

        $customerLegacy = new CustomerLegacy();
        $customer = $customerLegacy->find($code);
        if(!$customer) return redirect()->route('customer.create')->with('status', 'Customer does not exists');
        if(strlen(str_replace(['.', '-', '/'], '', $customer->cnpj_cpf)) != 14){
            $isUser = true;
            $routerStore = 'customer.user.store';
        }else{
            $isUser = false;
            $customer->municipal_registration = '';
            $routerStore = 'customer.store';
        }

        return view('customers.create', compact('customer', 'isUser', 'routerStore'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(CustomerRequest $request)
    {
        $company = Company::create($request->all());

        $customer = new Customer($request->all());
        $customer->typeable()->associate($company);

        $customer->save();

        return redirect()->route('customer.index')->withStatus(__('Customer successfully created.'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function storeUser(CustomerUserRequest $request)
    {
        $user = User::create($request->all());

        $customer = new Customer($request->all());
        $customer->typeable()->associate($user);

        $customer->save();

        return redirect()->route('customer.index')->withStatus(__('Customer successfully created.'));
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit(Customer $customer)
    {
        return view('customers.edit', compact('customer'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(CustomerRequest $request, Customer  $customer)
    {
        $customer->update($request->all());

        $customer->typeable()->update($request->all(['cnpj', 'social_reason', 'municipal_registration', 'state', 'phone', 'address']));

        return redirect()->route('customer.index')->withStatus(__('Customer successfully updated.'));
    }


    public function updateUser(CustomerUserRequest $request, Customer  $customer)
    {
        $customer->update($request->all());

        $hasPassword = $request->get('password');

        $attributes = $request->all(['name', 'email', 'cpf', 'phone', 'address']);
        $attributes = ($hasPassword) ? array_merge($attributes, ['password']): $attributes;
        $customer->typeable()->update($attributes);

        return redirect()->route('customer.index')->withStatus(__('Customer successfully updated.'));
    }


    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Customer  $customer)
    {
        if($customer->typeable_type == Company::class)
            $customer->typeable->delete();

        $customer->delete();

        return redirect()->route('customer.index')->withStatus(__('Customer successfully deleted.'));
    }
}
