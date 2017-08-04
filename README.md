# allpay2in1

整合綠界線上刷卡及超商物流

本repository 部份fork自ScottChayaa的https://github.com/ScottChayaa/Allpay ，謹此聲明。

適用對象:以Laravel 5開發商務網站，欲使用綠界線上金流、物流服務。

實作版本：Laravel 5.2

本版的線上金流程式庫因當初專案需求，先引用歐付寶的Library，但後來因有超商到店付的需求，所以轉而使用綠界的線上金流＆物流服務，為什麼是綠界！？

歐付寶跟綠界基本上是同一家公司，歐付寶現在主推行動支付，物流的業務又撥回給先前購併的綠界，為求省事只好將金流與物流全轉到綠界。

還好兩家公司的金流API參數都一致，所以置換上並無多大困擾。後續有時間再改回Ecpay自家的程式庫。

若有超商貨到付款的需求者，建議與綠界簽約時，最好金流、物流都一起簽，省的到時要加簽又要花好幾天時間。

step 1 : Download the package

composer命令安裝	

composer require kennychou3896/allpay2in1 dev-master

或者是新增package至composer.json

"require": {
  "kennychou3896/allpay2in1": "dev-master"
},

然後更新安裝

composer update

或全新安裝

composer install


**step 2 : Modify config file**

增加`config/app.php`中的`providers`和`aliases`的參數 。

'providers' => [ // ... Kennychou3896\Allpay2in1\AllpayServiceProvider::class, ]

'aliases' => [ // ... 'Allpay' => Kennychou3896\Allpay2in1\Facade\Allpay::class, ]


**step 3 : Publish config to your project**

執行下列命令，將package的config檔配置到你的專案中

php artisan vendor:publish

至config/allpay.php中確認Allpay設定：

    return [
        'ServiceURL' => 'http://payment-stage.ecpay.com.tw/Cashier/AioCheckOut',    
        'HashKey'    => '5294y06JbISpM5x9',    //這是綠界給的test Key ，正式上線由此抽換為你的Key
        'HashIV'     => 'v77hoKGq4kWxNNIS',    
        'MerchantID' => '2000132',    
    ];


**How To Use -->線上刷卡篇

在Controller中
      
    use Allpay; 
    public function Demo()
    {   
      //Official Example :     
      //https://github.com/allpay/PHP/blob/master/AioSDK/example/sample_Credit_CreateOrder.php
    
      //基本參數(可依系統規劃自行調整)
      Allpay::i()->Send['ReturnURL']         = "http://www.yourwebsites.com.tw/ReturnURL" ; 
                                            //交易結果回報的網址
      Allpay::i()->Send['ClientBackURL']     = "http://www.yourwebsites.com.tw/ClientBackURL" ; 
                                            //交易結束，讓user導回的網址
      Allpay::i()->Send['MerchantTradeNo']   = "Test".time() ;           //訂單編號
      Allpay::i()->Send['MerchantTradeDate'] = date('Y/m/d H:i:s');      //交易時間
      Allpay::i()->Send['TotalAmount']       = 2000;                     //交易金額
      Allpay::i()->Send['TradeDesc']         = "good to drink" ;         //交易描述
      Allpay::i()->Send['EncryptType']      = '1' ;  
      Allpay::i()->Send['ChoosePayment']     = "Credit" ;     //付款方式:信用卡
      Allpay::i()->Send['PaymentType']        = 'aio' ;

      //訂單的商品資料
      array_push(Allpay::i()->Send['Items'], 
              array('Name' => "美美小包包", 
              'Price' => (int)"2000",'Currency' => "元", 
              'Quantity' => (int) "1", 
              'URL' => "http://www.yourwebsites.com.tw/Product"));

      //Go to EcPay    
      echo "線上刷卡頁面導向中...";    
      echo Allpay::i()->CheckOutForm();
    
      //開發階段，如果你希望看到表單的內容，可以改為以下敘述：   
      //echo Allpay::i()->CheckOutForm('按我，才送出');
    
    }

**超商物流篇--到店付**
    
    1.選擇『到付店』：
      Allpay::l()->Send['MerchantTradeNo'] = 'Test-'.date('YmdHis');
      Allpay::l()->Send['LogisticsSubType'] = 'UNIMARTC2C'; //或FAMIC2C,全家
      Allpay::l()->Send['IsCollection'] = 'N';//是否代收貨款
      Allpay::l()->Send['ServerReplyURL'] = url('shop_option_reply'); //超商系統回覆路徑post
      Allpay::l()->Send['ExtraData'] = ''; //附帶資料
      Allpay::l()->Send['Device'] = '0';		
      $logisticsForm = Allpay::l()->CvsMap();
      echo $logisticsForm;
    
    2.取得『到付店』之回覆資訊：
      $data = array();		
      $data['merchant_trade_no'] = $request->input('MerchantTradeNo'); //訂單編號
      $data['LogisticsSubType'] = $request->input('LogisticsSubType'); //物流通路代碼,如統一:UNIMART
      $data['CVSStoreID'] = $request->input('CVSStoreID');//商店代碼
      $data['CVSStoreName'] = $request->input('CVSStoreName');
      $data['CVSAddress'] = $request->input('CVSAddress');//User 所選之超商店舖地址
      $data['CVSTelephone'] = $request->input('CVSTelephone');//User 所選之超商店舖電話
      $data['ExtraData'] = $request->input('ExtraData');//額外資訊,原資料回傳
    
    3.產生『到付店』托運單：
    
    //背景建立店到付物流單
    	try {
		$AL = Allpay::l();
	  	$AL->HashKey = config('allpay.HashKey');
	  	$AL->HashIV = config('allpay.HashIV');
	        $AL->Send = array(
	            'MerchantID' => config('allpay.MerchantID'),
	            'MerchantTradeNo' => 'mic-' . date('YmdHis'),
	            'MerchantTradeDate' => date('Y/m/d H:i:s'),
	            'LogisticsType' => 'CVS',
	            'LogisticsSubType' => 'UNIMARTC2C',
	            'GoodsAmount' => 100,
	            'CollectionAmount' => 100,
	            'IsCollection' => 'Y',    //是否代收貨款
	            'GoodsName' => '商品名稱',
	            'SenderName' => '李小華',
	            'SenderPhone' => '0226550115',
	            'SenderCellPhone' => '0911222333',
	            'ReceiverName' => '周大大',
	            'ReceiverPhone' => '0233881234',
	            'ReceiverCellPhone' => '0912555666',
	            'ReceiverEmail' => 'user@email.com',
	            'TradeDesc' => '測試交易敘述',
	            'ServerReplyURL' => url('logistics_order_reply'),        //物流狀態回覆網址
	            'LogisticsC2CReplyURL' => url('logistics_order_C2C_reply'),    //到付店若有異動訊息回覆網址
	            'Remark' => '測試備註',
	            'PlatformID' => '',
	        );
	        $AL->SendExtend = array(
	             'ReceiverStoreID' => '136392',     //到付店id
	             'ReturnStoreID' => '991182'        //回退店id,一般與寄件店id同
	        );
		$Result = $AL->BGCreateShippingOrder();   //超商系統回覆內容
		echo '<pre>' . print_r($Result, true) . '</pre>';          
          	if($Result['RtnCode'] == 300){
            		//托運單成功建立

          	}
	} catch(Exception $e) {
		$Result = $e->getMessage();
          	echo $e->getMessage();
    	} 
    3.1 取消『到付店』托運單(僅統一超商)：
        // 取消物流單(統一超商C2C)
        try {
          $AL = Allpay::l();
          $AL->HashKey = config('allpay.HashKey');
          $AL->HashIV = config('allpay.HashIV');
            $AL->Send = array(
            'MerchantID' => config('allpay.MerchantID'),
            'AllPayLogisticsID' => $ships->AllPayLogisticsID,     //綠界物流編號
                'CVSPaymentNo' => $ships->CVSPaymentNo,        		//統一超商寄貨單號
                'CVSValidationNo' => $ships->CVSValidationNo,         //驗證碼
                'PlatformID' => ''
          );
          $Result = $AL->CancelUnimartLogisticsOrder();
          //	echo '<pre>' . print_r($Result, true) . '</pre>';

         } catch(Exception $e) {
            $Result = $e->getMessage();
            echo $e->getMessage();
         } 
        
    4.列印『到付店』托運＆繳款單：
      //統一超商
			try {
		        $AL = Allpay::l();
		        $AL->HashKey = config('allpay.HashKey');
		        $AL->HashIV = config('allpay.HashIV');
		        $AL->Send = array(
		            'MerchantID' => config('allpay.MerchantID'),
		            'AllPayLogisticsID' => $Result['AllPayLogisticsID'],
		            'CVSPaymentNo' => $Result['CVSPaymentNo'],
		            'CVSValidationNo' => $Result['CVSValidationNo'],
		            'PlatformID' => ''
		        );
		        // PrintUnimartC2CBill(Button名稱, Form target)
		        $html = $AL->PrintUnimartC2CBill();  //'列印繳款單(統一超商C2C)'
		        echo $html;
		    } catch(Exception $e) {
		        echo $e->getMessage();
		    }
        //全家
        try {
              $AL = Allpay::l();
              $AL->HashKey = config('allpay.HashKey');
              $AL->HashIV = config('allpay.HashIV');
              $AL->Send = array(
                  'MerchantID' => config('allpay.MerchantID'),
                  'AllPayLogisticsID' => $Result['AllPayLogisticsID'],
                  'CVSPaymentNo' => $Result['CVSPaymentNo'],
                  'PlatformID' => ''
              );
              // PrintFamilyC2CBill(Button名稱, Form target)
              $html = $AL->PrintFamilyC2CBill(); //'全家列印小白單(全家超商C2C)'
              echo $html;  
          } catch(Exception $e) {
              echo $e->getMessage();
          }
    
