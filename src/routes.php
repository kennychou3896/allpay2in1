<?php

Route::group([
    'namespace' => 'kennychou3896\Allpay\Controllers',
    'prefix'    => 'allpay_demo'],
    function () {
        Route::get('/', 'DemoController@index');
        Route::get('/checkout', 'DemoController@checkout');
    }
);
