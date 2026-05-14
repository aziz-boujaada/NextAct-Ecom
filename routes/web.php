<?php

use App\Http\Controllers\Api\InvoiceController;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

use Illuminate\Support\Facades\Mail;

Route::get('/test-mail', function () {

    try {
        Mail::raw('Test Gmail SMTP', function ($message) {
            $message->to('boujaadaaziz2911@gmail.com')
                ->subject('Test Gmail');
        });

        return 'Email sent';
    } catch (\Exception $e) {
        Log::error($e->getMessage());

        return $e->getMessage();
    }
});