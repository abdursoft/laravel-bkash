<?php
namespace Abdursoft\LaravelBkash;

use Exception;

class Bkash {
    protected $base_url;
    protected $callbackURL;
    protected $token;
    protected $username;
    protected $password;
    protected $paymentMode;
    protected $appKey;
    protected $appSecret;

    public function __construct($username,$password,$appKey,$appSecret,$paymentMode,$callbackURL)
    {
        $this->appKey = $appKey;
        $this->appSecret = $appSecret;
        $this->password = $password;
        $this->username = $username;
        $this->callbackURL = $callbackURL;

        if (strtolower($paymentMode) == 'sandbox') {
            $this->base_url = 'https://tokenized.sandbox.bka.sh/v1.2.0-beta/tokenized/checkout';
        }else{
            $this->base_url = 'https://tokenized.pay.bka.sh/v1.2.0-beta/tokenized/checkout';
        }
    }

    public function token()
    {
        $request_data = array(
            'app_key' => $this->appKey,
            'app_secret' => $this->appSecret
        );
        $url = curl_init("$this->base_url/token/grant");
        $request_data_json = json_encode($request_data);
        $header = array(
            "Content-Type:application/json",
            "username:" . $this->username,
            "password:" . $this->password
        );
        curl_setopt($url, CURLOPT_HTTPHEADER, $header);
        curl_setopt($url, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($url, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($url, CURLOPT_POSTFIELDS, $request_data_json);
        curl_setopt($url, CURLOPT_FOLLOWLOCATION, 1);

        $result = curl_exec($url);
        curl_close($url);
        $accessToken = json_decode($result, true);
        return $accessToken['id_token'];
    }

    public function paymentCreate($ext,$amount,$invoice, $callbackURL)
    {
        $requestbody = array(
            'mode' => '0011',
            'amount' => $amount,
            'currency' => 'BDT',
            'intent' => 'sale',
            'payerReference' => $ext,
            'merchantInvoiceNumber' => $invoice,
            'callbackURL' => $callbackURL
        );
        $requestbodyJson = json_encode($requestbody);

        $resultdata = $this->requestHandler('POST',$requestbodyJson,"$this->base_url/create");
        $obj = json_decode($resultdata, true);
        return $obj;
    }

    public function paymentExecute($paymentID)
    {
        $request_body = array(
            'paymentID' => $paymentID
        );

        $resultdata = $this->requestHandler('POST',json_encode($request_body),"$this->base_url/execute");
        $obj = json_decode($resultdata, true);
        return $obj;
    }

    public function paymentQuery($paymentID)
    {
        $requestbody = array(
            'paymentID' => $paymentID
        );
        $resultdata = $this->requestHandler('POST',json_encode($requestbody),"$this->base_url/payment/status");
        $obj = json_decode($resultdata, true);
        return $obj;
    }

    public function transactionQuery($txn)
    {
        $requestbody = array(
            'trxID' => $txn
        );
        $resultdata = $this->requestHandler('POST',json_encode($requestbody),"$this->base_url/general/searchTransaction");
        $obj = json_decode($resultdata, true);
        return $obj;
    }

    public function refund($paymentID, $transactionID, $amount, $sku, $reason)
    {
        $requestbody = array(
            'paymentID' => $paymentID,
            'trxID'     => $transactionID,
            'amount'    => $amount,
            'reason'    => $reason,
            'sku'       => $sku
        );
        $resultdata = $this->requestHandler('POST',json_encode($requestbody),"$this->base_url/payment/refund");
        $obj = json_decode($resultdata, true);
        return $obj;
    }

    protected function requestHandler($method,$body,$url){
        try {
            $header = array(
                'Content-Type:application/json',
                'authorization:' . $this->token(),
                'x-app-key:' . $this->appKey
            );
            $requestbodyJson = json_encode($body);
            curl_setopt($url, CURLOPT_HTTPHEADER, $header);
            curl_setopt($url, CURLOPT_CUSTOMREQUEST, $method);
            curl_setopt($url, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($url, CURLOPT_POSTFIELDS, $requestbodyJson);
            curl_setopt($url, CURLOPT_FOLLOWLOCATION, 1);
            $resultdatax = curl_exec($url);
            $obj = json_decode($resultdatax, true);
            curl_close($url);
            return $obj;
        } catch (\Throwable $th) {
            new Exception("Invalid request or data format",400);
        }
    }
}