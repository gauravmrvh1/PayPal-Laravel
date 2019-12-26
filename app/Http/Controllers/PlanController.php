<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use PayPal\Api\ChargeModel;
use PayPal\Api\Currency;
use PayPal\Api\MerchantPreferences;
use PayPal\Api\PaymentDefinition;
use PayPal\Api\Plan;

use PayPal\Api\Patch;
use PayPal\Api\PatchRequest;
use PayPal\Common\PayPalModel;

class PlanController extends Controller
{
    
    // http://paypal.github.io/PayPal-PHP-SDK/sample/#billing

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
     * 
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
     * 
     */
    public function getPlans(){
        $planId = 'P-96689664189973931IJGZFPA';
        try {
            $plan = Plan::get($planId, $this->apiContext);
            echo '<pre>';
            return $plan;
        } catch (Exception $ex) {

            dd($e->getMessage());
        }
    }

    /**
     * 
     */
    public function updatePlan()
    {
        # code...
        try {
            $patch = new Patch();
        
            $value = new PayPalModel('{
                "state":"ACTIVE"
                }'
            );
        
            $patch->setOp('replace')
                ->setPath('/')
                ->setValue($value);

            $patchRequest = new PatchRequest();
            $patchRequest->addPatch($patch);
        
            $createdPlan->update($patchRequest, $apiContext);
        
            $plan = Plan::get($createdPlan->getId(), $apiContext);
        } catch (Exception $ex) {

        }
    }
}
