<?php

Route::group([
    'namespace' => 'Kennychou3896\Allpay2in1\Controllers',
    'prefix'    => 'allpay_demo'],
    function () {
        Route::get('/', 'DemoController@index');
        Route::get('/checkout', 'DemoController@checkout');
    }
);
