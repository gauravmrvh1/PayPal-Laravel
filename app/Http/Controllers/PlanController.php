<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Exception;
use PayPal\Api\ChargeModel;
use PayPal\Api\Currency;
use PayPal\Api\MerchantPreferences;
use PayPal\Api\PaymentDefinition;
use PayPal\Api\Plan;

use PayPal\Api\Patch;
use PayPal\Api\PatchRequest;
use PayPal\Common\PayPalModel;
use PayPal\Api\Agreement;


class PlanController extends Controller
{
    
    // http://paypal.github.io/PayPal-PHP-SDK/sample/#billing

    private $apiContext;

    public function __construct(){
        $this->apiContext = new \PayPal\Rest\ApiContext(
            new \PayPal\Auth\OAuthTokenCredential(
                env('PAYPAL_CLIENT_ID', ''),            // ClientID
                env('PAYPAL_CLIENT_SECRET', '')         // ClientSecret
            )
        );
    }

    /**
     * @desc: Payment definitions for this billing plan
     */
    public function createPlan( Request $request ){
        // ======================================Create Plan Functionality========================================
        $plan = new Plan();
        $plan->setName('T-Shirt of the Month Club Plan')
            ->setDescription('Template creation.')
            ->setType('fixed');
        
        $paymentDefinition = new PaymentDefinition();
        $paymentDefinition->setName('Regular Payments')
            ->setType('REGULAR')
            ->setFrequency('Month')
            ->setFrequencyInterval("2")
            ->setCycles("12")
            ->setAmount(new Currency(array('value' => 100, 'currency' => 'USD')));
        

        $chargeModel = new ChargeModel();
        $chargeModel->setType('SHIPPING')
            ->setAmount(new Currency(array('value' => 10, 'currency' => 'USD')));

        $paymentDefinition->setChargeModels(array($chargeModel));

        $merchantPreferences = new MerchantPreferences();
        $baseUrl = $request->url();

        $merchantPreferences->setReturnUrl("$baseUrl/ExecuteAgreement.php?success=true")
            ->setCancelUrl("$baseUrl/ExecuteAgreement.php?success=false")
            ->setAutoBillAmount("yes")
            ->setInitialFailAmountAction("CONTINUE")
            ->setMaxFailAttempts("0")
            ->setSetupFee(new Currency(array('value' => 1, 'currency' => 'USD')));
        
        $plan->setPaymentDefinitions(array($paymentDefinition));
        $plan->setMerchantPreferences($merchantPreferences);
            

        $request = clone $plan;

        try {

            $output = $plan->create($this->apiContext);

        }catch (\PayPal\Exception\PayPalConnectionException $ex) {
            // This will print the detailed information on the exception.
            //REALLY HELPFUL FOR DEBUGGING
            echo $ex->getCode(); // Prints the Error Code
            echo $ex->getData(); // Prints the detailed error message 
            die($ex);
        } 

        echo '<pre>';
        return $output;
    }

    /**
     * @name : PlanDetail
     */
    public function getPlanDetail($planId){
        if( empty($planId) ){
            $planId = 'P-96689664189973931IJGZFPA';
        }

        try {
            $plan = Plan::get($planId, $this->apiContext);
            echo '<pre>';
            return $plan;
        } catch (Exception $ex) {

            dd($e->getMessage());
        }
    }

    /**
     * @desc: Get List of Plan
     */
    public function getPlanList(Request $request){
        try{
            $params = array('page_size' => '10');
            $planList = Plan::all($params, $this->apiContext);
            echo '<pre>';
            return $planList;

        } catch (Exception $ex) {
            
        }
    }

    /**
     * @desc: Update a plan
     */
    public function updatePlan(Request $request)
    {
        # code...
        try {
            $planId = 'P-7RH89600HA168153XIYYTNRI';
            $createdPlan = $this->getPlanDetail($planId);
            // return $createdPlan;
            $patch = new Patch();
            $value = new PayPalModel('{
                "description":"Description"
                }'
            );
        
            $patch->setOp('replace')
                ->setPath('/')
                ->setValue($value);

            $patchRequest = new PatchRequest();
            $patchRequest->addPatch($patch);

            // echo '<pre>';
            // echo $patchRequest;
            // die;
        
            $createdPlan->update($patchRequest, $this->apiContext);
            $plan = Plan::get($planId, $this->apiContext);

            return $plan;

        } catch (Exception $ex) {

        }
    }

    /**
     * @desc: Delete Plan
     */
    public function deletePlan(Request $request){
        $planId = $request->id;
        $createdPlan = $this->getPlanDetail($planId);

        try {
            $result = $createdPlan->delete($this->apiContext);

        } catch (Exception $ex) {

        }

        echo '<pre>';
        echo $result;
        die;

    }
}
