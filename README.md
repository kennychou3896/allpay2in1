# Allpay2in1

整合綠界線上刷卡及超商物流

本repository 部份fork自ScottChayaa的https://github.com/ScottChayaa/Allpay，謹此聲明。

試用對象:以Laravel 5開發電子商務網站，欲使用綠界線上金流、物流服務。

本版的線上金流程式庫因當初專案需求，先引用歐付寶的Library，但後來因有超商到店付的需求，所以轉而使用綠界的線上金流＆物流服務，為什麼是綠界！？

歐付寶跟綠界基本上是同一家公司，歐付寶現在主推行動支付，物流的業務又撥回給先前購併的綠界，為求省事將金流與物流轉到綠界。

還好兩家公司的金流API參數都一致，所以置換上並無多大困擾。後續有時間再改回Ecpay自家的程式庫。

實作版本：Laravel 5.2

step 1 : Download the package

composer命令安裝	

composer require kennychou3896/Allpay2in1 dev-master

或者是新增package至composer.json

"require": {
  "Kennychou3896/allpay": "dev-master"
},

然後更新安裝

composer update

或全新安裝

composer install


**step 2 : Modify config file**
增加`config/app.php`中的`providers`和`aliases`的參數 。
providers' => [ // ... Kennychou3896\Allpay\AllpayServiceProvider::class, ]

'aliases' => [ // ... 'Allpay' => Kennychou3896\Allpay\Facade\Allpay::class, ]


**step 3 : Publish config to your project**
執行下列命令，將package的config檔配置到你的專案中

php artisan vendor:publish

至config/allpay.php中確認Allpay設定：

return [
    'ServiceURL' => 'http://payment-stage.ecpay.com.tw/Cashier/AioCheckOut',
    'HashKey'    => '5294y06JbISpM5x9',
    'HashIV'     => 'v77hoKGq4kWxNNIS',
    'MerchantID' => '2000132',
];


How To Use--->線上刷卡

在Controller中

use Allpay;

public function Demo()
{
    //Official Example : 
    //https://github.com/allpay/PHP/blob/master/AioSDK/example/sample_Credit_CreateOrder.php
    
    //基本參數(請依系統規劃自行調整)
    Allpay::i()->Send['ReturnURL']         = "http://www.yourwebsites.com.tw/ReturnURL" ; //交易結果回報的網址
    Allpay::i()->Send['ClientBackURL']     = "http://www.yourwebsites.com.tw/ClientBackURL" ; //交易結束，讓user導回的網址
    Allpay::i()->Send['MerchantTradeNo']   = "Test".time() ;           //訂單編號
    Allpay::i()->Send['MerchantTradeDate'] = date('Y/m/d H:i:s');      //交易時間
    Allpay::i()->Send['TotalAmount']       = 2000;                     //交易金額
    Allpay::i()->Send['TradeDesc']         = "good to drink" ;         //交易描述
    Allpay::i()->Send['EncryptType']      = '1' ;  
    Allpay::i()->Send['ChoosePayment']     = "Credit" ;     //付款方式:信用卡
    Allpay::i()->Send['PaymentType']        = 'aio' ;

    //訂單的商品資料
    array_push(Allpay::i()->Send['Items'], array('Name' => "美美小包包", 'Price' => (int)"2000",
               'Currency' => "元", 'Quantity' => (int) "1", 'URL' => "http://www.yourwebsites.com.tw/Product"));

    //Go to EcPay
    echo "線上刷卡頁面導向中...";
    echo Allpay::i()->CheckOutForm();
   //開發階段，如果你希望看到表單的內容，可以改為以下敘述：
    //echo Allpay::i()->CheckOutForm('按我，才送出');
    
}
