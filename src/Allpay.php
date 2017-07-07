<?php

namespace Kennychou3896\Allpay2in1;

class Allpay
{

    private $instance = null;
	private $logistics = null;

    //--------------------------------------------------------

    public function __construct()
    {
        $this->instance = new \AllInOne();

        $this->instance->ServiceURL = config('allpay.ServiceURL');
        $this->instance->HashKey    = config('allpay.HashKey');
        $this->instance->HashIV     = config('allpay.HashIV');
        $this->instance->MerchantID = config('allpay.MerchantID');
		require(__DIR__.'/lib/ECPay.Logistics.Integration.php');
		$this->logistics = new \ECPayLogistics();
      //  $this->logistics->ServiceURL = config('allpay.ServiceURL');
        $this->logistics->Send['HashKey']    = config('allpay.HashKey');
        $this->logistics->Send['HashIV']     = config('allpay.HashIV');
        $this->logistics->Send['MerchantID'] = config('allpay.MerchantID');
    }

    public function instance()
    {
        return $this->instance;
    }

    public function i()
    {
        return $this->instance;
    }
    public function logistics()
    {
        return $this->logistics;
    }

    public function l()
    {
        return $this->logistics;
    }

}
