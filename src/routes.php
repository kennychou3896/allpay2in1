<?php

Route::group([
    'namespace' => 'Kennychou3896\Allpay\Controllers',
    'prefix'    => 'allpay_demo_201707'],
    function () {
        Route::get('/', 'DemoController@index');
        Route::get('/checkout', 'DemoController@checkout');
    }
);
