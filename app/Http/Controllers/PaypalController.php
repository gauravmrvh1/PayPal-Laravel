<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;


use PayPal\Api\Amount;
use PayPal\Api\Details;
use PayPal\Api\Item;
use PayPal\Api\ItemList;
use PayPal\Api\Payer;
use PayPal\Api\Payment;
use PayPal\Api\RedirectUrls;
use PayPal\Api\Transaction;






class PaypalController extends Controller
{

    private $apiContext;

    public function __construct(){
        $this->apiContext = new \PayPal\Rest\ApiContext(
            new \PayPal\Auth\OAuthTokenCredential(
                'Aa0pTYPREBpQcuKR9lWyHEg_vv9xxLlnk9GTQkB0eTGU04hscEjiolVpeoTBkFLZaJ6ceXnqvJcNEXCn',     // ClientID
                'EHxv7kPEtaiPZPsnh_h33jnFm-1zIbTsqdepQwd5874mAeILcoexsciquUpnkBDRVQFDl13J2eQGMi-U'      // ClientSecret
            )
        );
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        // dd('paypal');
        // dd( env('PAYPAL_SANDBOX_API_USERNAME', '') );

        $apiContext = new \PayPal\Rest\ApiContext(
            new \PayPal\Auth\OAuthTokenCredential(
                'Aa0pTYPREBpQcuKR9lWyHEg_vv9xxLlnk9GTQkB0eTGU04hscEjiolVpeoTBkFLZaJ6ceXnqvJcNEXCn',     // ClientID
                'EHxv7kPEtaiPZPsnh_h33jnFm-1zIbTsqdepQwd5874mAeILcoexsciquUpnkBDRVQFDl13J2eQGMi-U'      // ClientSecret
            )
        );

        /* $apiContext = new \PayPal\Rest\ApiContext(
            new \PayPal\Auth\OAuthTokenCredential(
                'AYSq3RDGsmBLJE-otTkBtM-jBRd1TCQwFf9RGfwddNXWz0uFU9ztymylOhRS',     // ClientID
                'EGnHDxD_qRPdaLdZz8iCr8N7_MzF-YHPTkjs6NKYQvQSBngp4PTTVWkPZRbL'      // ClientSecret
            )
        ); */

        $payer = new \PayPal\Api\Payer();
        $payer->setPaymentMethod('paypal');

        
        $item1 = new Item();
        $item1->setName('Ground Coffee 40 oz')
            ->setCurrency('INR')
            ->setQuantity(1)
            ->setSku("123123") // Similar to `item_number` in Classic API
            ->setPrice(7.5);
            $item2 = new Item();
            $item2->setName('Granola bars')
            ->setCurrency('INR')
            ->setQuantity(5)
            ->setSku("321321") // Similar to `item_number` in Classic API
            ->setPrice(2);
            
        $itemList = new ItemList();
        $itemList->setItems(array($item1, $item2));
            
        // dd($itemList);


        $details = new Details();
        $details->setShipping(1.2)
            ->setTax(1.3)
            ->setSubtotal(17.50);


        $amount = new Amount();
        $amount->setCurrency("INR")
            ->setTotal(20)
            ->setDetails($details);

        // $amount = new \PayPal\Api\Amount();
        // $amount->setTotal('1.00');
        // $amount->setCurrency('INR');
        // dd( $amount );


        $transaction = new Transaction();
        $transaction->setAmount($amount)
            ->setItemList($itemList)
            ->setDescription("Payment description")
            ->setInvoiceNumber(uniqid());
        // $transaction = new \PayPal\Api\Transaction();
        // $transaction->setAmount($amount);
        // dd( $transaction );


        $baseUrl = $request->url();
        // dd($request);
        $redirectUrls = new RedirectUrls();
        $redirectUrls->setReturnUrl("$baseUrl/ExecutePayment?success=true")
            ->setCancelUrl("$baseUrl/ExecutePayment?success=false");
        // $redirectUrls = new \PayPal\Api\RedirectUrls();
        // $redirectUrls->setReturnUrl("https://example.com/your_redirect_url.html")
        //     ->setCancelUrl("https://example.com/your_cancel_url.html");

        // dd($redirectUrls);
        $payment = new Payment();
        $payment->setIntent('sale')
            ->setPayer($payer)
            ->setTransactions(array($transaction))
            ->setRedirectUrls($redirectUrls);

        $request = clone $payment;
        // dd($request);
        // dd($payment);

        /* $payment = new \PayPal\Api\Payment(
            array(
                "intent" => "sale",
                "redirect_urls" => array(
                    "return_url" => "https://example.com/your_redirect_url.html",
                    "cancel_url" => "https://example.com/your_cancel_url.html"
                ),
                "payer" => array(
                    "payment_method" => "paypal"
                ),
                "transactions" => array(
                    array(
                        "amount" => array(
                            "total" => "1.00",
                            "currency" => "USD"
                        )
                    )
                )
            )
        );
        // This will print the intent
        echo $payment->getIntent(); */


        // After Step 3
        try {

            $payment->create($apiContext);
            $approvalUrl = $payment->getApprovalLink();

            return redirect()->away($approvalUrl);
            // echo $payment;
            // echo "\n\nRedirect user to approval_url: " . $approvalUrl . "\n";
        }

        catch (\PayPal\Exception\PayPalConnectionException $ex) {

            // ResultPrinter::printError("Created Payment Using PayPal. Please visit the URL to Approve.", "Payment", null, $request, $ex);
            // exit(1);

            // This will print the detailed information on the exception.
            //REALLY HELPFUL FOR DEBUGGING

            echo '<pre>';
            // echo $ex->getCode(); // Prints the Error Code
            echo $ex->getData(); // Prints the detailed error message 
            die;
            die($ex);
        } 
        
        catch (Exception $ex) {
            die($ex);
        }

    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create(Request $request)
    {
        dd($request->all());
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id = null)
    {
        try {
            $payment = Payment::get($createdPayment->getId(), $this->apiContext);
            echo '<pre>';
            die;
        } catch (Exception $ex) {

        }
        
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}
