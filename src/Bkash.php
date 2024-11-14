<?php
namespace Abdursoft\LaravelBkash;

class Bkash {
    protected $base_url;
    protected $callbackURL;
    protected $token;
    protected $username;
    protected $password;
    protected $paymentMode;
    protected $appKey;
    protected $appSecret;

    /**
     * Initialize bkash payment
     */
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

    /**
     * Create bkash token
     */
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

    /**
     * Create payment intent
     */
    public function paymentCreate($reference,$amount,$invoice)
    {
        $requestbody = array(
            'mode' => '0011',
            'amount' => $amount,
            'currency' => 'BDT',
            'intent' => 'sale',
            'payerReference' => $reference,
            'merchantInvoiceNumber' => $invoice,
            'callbackURL' => $this->callbackURL
        );
        return $this->requestHandler('POST',$requestbody,"$this->base_url/create");
    }

    /**
     * Payment execute
     */
    public function paymentExecute($paymentID)
    {
        $request_body = array(
            'paymentID' => $paymentID
        );

        return $this->requestHandler('POST',$request_body,"$this->base_url/execute");
    }

    /**
     * Payment query | Searching
     */
    public function paymentQuery($paymentID)
    {
        $requestbody = array(
            'paymentID' => $paymentID
        );
        return $this->requestHandler('POST',$requestbody,"$this->base_url/payment/status");
    }

    /**
     * Payment capture
     */
    public function paymentCapture($paymentID){
        return $this->requestHandler('POST',null,"$this->base_url/payment/capture/$paymentID");
    }

    /**
     * Payment query
     */
    public function transactionQuery($txn)
    {
        $requestbody = array(
            'trxID' => $txn
        );
        return $this->requestHandler('POST',$requestbody,"$this->base_url/general/searchTransaction");
    }

    /**
     * Refunc a payment
     */
    public function refund($paymentID, $transactionID, $amount, $sku, $reason)
    {
        $requestbody = array(
            'paymentID' => $paymentID,
            'trxID'     => $transactionID,
            'amount'    => $amount,
            'reason'    => $reason,
            'sku'       => $sku
        );
        return $this->requestHandler('POST',$requestbody,"$this->base_url/payment/refund");
    }

    /**
     * Payout users
     */
    public function payout($amount, $invoice, $msisdn){
        $body = [
            "amount" => $amount,
            "currency" => "BDT",
            "merchantInvoiceNumber" => $invoice,
            "receiverMSISDN" => $msisdn
        ];

        return $this->requestHandler('POST',$body,"$this->base_url/payment/b2cPayment");
    }

    /**
     * Request handler
     */
    protected function requestHandler($method,$body=null,$url){
        $header = array(
            'Content-Type:application/json',
            'Authorization:' . $this->token(),
            'X-APP-Key:' . $this->appKey
        );
        $url = curl_init($url);
        curl_setopt($url, CURLOPT_HTTPHEADER, $header);
        curl_setopt($url, CURLOPT_CUSTOMREQUEST, $method);
        curl_setopt($url, CURLOPT_RETURNTRANSFER, true);
        $body !== null ? curl_setopt($url, CURLOPT_POSTFIELDS, json_encode($body)) : "";
        curl_setopt($url, CURLOPT_FOLLOWLOCATION, 1);
        $resultdatax = curl_exec($url);
        $obj = json_decode($resultdatax, true);
        curl_close($url);
        return $obj;
    }
}
