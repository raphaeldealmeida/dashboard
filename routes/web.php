<?php
use \Illuminate\Support\Facades\Storage;
use \Illuminate\Support\Facades\DB;

Auth::routes(['register' => false]);

Route::get('/', 'HomeController@index')->name('home')->middleware('auth');

Route::group(['middleware' => 'auth'], function () {
	Route::get('table-list', function () {
		return view('pages.table_list');
	})->name('table');

	Route::resource('user', 'UserController', ['except' => ['show']])->middleware('can:isAdmin');
	Route::get('profile', ['as' => 'profile.edit', 'uses' => 'ProfileController@edit']);
	Route::put('profile', ['as' => 'profile.update', 'uses' => 'ProfileController@update']);
	Route::put('profile/password', ['as' => 'profile.password', 'uses' => 'ProfileController@password']);

    Route::resource('call', 'CallController');
    Route::resource('invoice', 'InvoiceController');
    Route::get('invoice/{invoice}/csv', ['as' => 'invoice.csv', 'uses' => 'InvoiceController@csv']);
    Route::get('invoice/{invoice}/billet', ['as' => 'invoice.billet', 'uses' => 'InvoiceController@billet']);
    Route::resource('customer', 'CustomerController')->middleware('can:isAdmin');
    Route::post('customer/user/', ['as' => 'customer.user.store', 'uses' => 'CustomerController@storeUser']);
    Route::put('customer/{customer}/user/', ['as' => 'customer.user.update', 'uses' => 'CustomerController@updateUser']);

    Route::get('company/{code}/remote', ['as' => 'company.show.remote', function ($code, \App\Services\CompanyService $service) {
        return $service->getRemote($code);
    }]);

    Route::get('call/audio/{uuid}/play', ['as' => 'call.audio', function($uuid){
        $record = DB::table(env('DB_DATABASE_VOIP') . '.gravacoes_s3')
            ->where('uuid', $uuid)->limit('1')->get()->first();

        $urlExploded = explode('/', $record->path_s3);
        $bucket = $urlExploded[2];
        $fileUrl = implode('/', array_slice($urlExploded, 3));

        $storage = Storage::createS3Driver(array_merge(config('filesystems.disks.s3'), ['bucket' => $bucket]));

        return $storage->download($fileUrl, null, ['bucket' => $bucket]);
    }]);
});

