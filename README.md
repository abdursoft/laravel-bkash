# Bkash tokenized payment gateway integration for PHP and Laravel applicaation

Now you can easily integrate bkash tokenized payment gateway into your PHP and laravel application. Just follow up the below process. Hope you will integrate the Bkash payment gateway successfully in your PHP or Laravel application

Now let's start with abdursoft/laravel-bkash. Run the below command to install the package.
<pre>composer require abdursoft/laravel-bkash</pre>
 

After installing the package you have to update ``.env`` file from your root folder with the below code.
<pre>
BKASH_USERNAME=017xxxxxxx
BKASH_PASSWORD=D7xxxxxxxx
BKASH_APP_KEY=APKxxxxxxxx
BKASH_APP_SECRET=APSxxxxxxx
</pre>
[Note] Remember you have to update these variable with your credentials.

If the package/library successfully installed on your project then make a controller in your laravel project.
<pre>php artisan make:controller BkashController</pre>

If your controller has been successfully created, open with your text editor and replace all code with below code.
<pre>
<?php

namespace App\Http\Controllers;

use Abdursoft\LaravelBkash\Bkash;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Validator;

class BkashController extends Controller
{

    protected $bkash;
    public function __construct()
    {

        $this->bkash = new Bkash(env('BKASH_USERNAME'),env('BKASH_PASSWORD'),env('BKASH_APP_KEY'),env('BKASH_APP_SECRET'),'sandbox',"http://127.0.0.1:8000/bkash/callback"); //use sandbox for sandbox testing and production for production service
    }

    /**
     * Create payment
     */
    public function createPayment(Request $request){
        $validator = Validator::make($request->all(),[
            'amount' => 'required',
            'reference' => 'required',
        ]);

        if($validator->fails()){
            return response()->json($validator->errors());
        }

        $payment = $this->bkash->paymentCreate($request->reference,$request->amount,uniqid());
        return redirect()->away($payment['bkashURL']);
    }

    /**
     * Refund payment
     */
    public function refundPayment(Request $request){
        $refund = $this->bkash->refund($request->id,$request->txn,$request->amount,$request->sku,$request->reason);
        if($refund['statusCode'] === '0000'){
            return redirect('/')->with('success',"Refund process has been completed");
        }else{
            return redirect('/')->with('error',"Refund process has been faild");
        }
    }

    /**
     * Bkash callback
     */
    public function callbackResponse(Request $request){
        if($request->query('status') == 'success'){
            $execute = $this->bkash->paymentExecute($request->query('paymentID'));
            Session::put(['paymentExecution' => $execute]);
            if($execute['statusCode'] == '0000'){
                return redirect('/')->with('success','Payment has been completed');
            }elseif($execute['statusCode'] == '2062'){
                return redirect('/')->with('success','Payment has already been compeled');
            }
        }elseif($request->query('status') == 'cancel'){
            return redirect('/')->with('error','Payment has been canceled');
        }elseif($request->query('status') == 'failure'){
            return redirect('/')->with('error','Payment has been faild');
        }else{
            return redirect('/')->with('error','Payment couldn\'t completed');
        }
    }
}
</pre>

At this stage you have to initalize the payment routes for baksh gateway. You can put the below routes or You can create your owns.
<pre>
Route::prefix('bkash')->group(function(){
    Route::post('payment', [BkashController::class, 'createPayment']);
    Route::post('refund', [BkashController::class, 'refundPayment']);
    Route::get('cancel', [BkashController::class, 'callbackResponse']);
    Route::get('callback', [BkashController::class, 'callbackResponse']);
});
</pre>
Don't forget to add ``use App\Http\Controllers\BkashController;`` in your route page.

To create a payment, you have to pass some data such as ``amount`` ``reference`` ``merchant_invoice_number`` ``callback_url``.
<pre>``amount`` for the payable amount.</pre>
<pre>``reference`` for the payment reference.</pre>
<pre>``merchant_invoice_number`` for futrue tracking the payment and remember it shuold be unique.</pre>
<pre>``callback_url`` bkash will redirect the user after payment processcing and you have to execute payment with this route's response.</pre>

To create a refund, you have to pass some data such as ``transactionID`` ``paymentID`` ``amount`` ``reason`` ``sku``
<pre>``transactionID`` use from a completed transaction.</pre>
<pre>``paymentID`` use the paymentID from above transaction.</pre>
<pre>``amount`` use a valid amount for refund and make sure the amount not bigger than above transaction.</pre>
<pre>``reason`` for refund the transaction.</pre>
<pre>``sku`` for the transaction processing.</pre>