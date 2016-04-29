<?php namespace BadChoice\Panama\PaymentGateways;
use Sermepa\Tpv\Tpv;

/**
 * Class Redsys
 *
 * This class helps you create payments through redsys online TPV (banc sabadell)
 *
 *    ## Configuration values
 *    The configuration values need to be set in config/services.php
 *
 *     'redsys' => [
 *          'secretKey'     => 'xxx',
 *          'merchantCode'  => 'xxx',
 *          'terminal'      => '001',
 *          'currency'      => '978',
 *          'merchantURL'   => 'xxx', //The silent url that will be called when payment completed (can be ok or not)
 *          'okURL'         => 'http://tocatoc.com/shop/payment/ok',
 *          'cancelURL'     => 'http://tocatoc.com/shop/payment/ko',*
 *          'titular'       => 'xxx',
 *
 *    ]
 *
 *    ## Usage
 *    To create a payment from a controller you can simply
 *    `return Redsys::createForm(true,$amount, "REVO " . $points." Points", "1235", $points)->redirect();`
 *
 *    It will redirect automatically to the bank pay page
 *
 *    If you want to show the payment button you can simply
 *    {!! Redsys::createForm(true,$amount, "REVO " . $points." Points", "1235", $points) !!}
 *
 *    The bank will do a silent call to `merchantUrl` and in that route you can do
 *    ```
 *    if(Redsys::isCompletedPaymentSuccessful()){
 *
 *    }
 *    else{
 *
 *    }
 *    ```
 *    Otherwise you can create a class instance to define further configuration items
 *
 *    ## TEST CARD
 *    nº targeta: 4548 8120 4940 0004
 *    nº targeta sense espais: 4548812049400004
 *    caducitat: 12/20 (Posar una data futura)
 *    cvv2: 123
 *    cip: 123456
 *
 *   ## TEST CARD TO FAIL
 *   nº targeta: 1111111111111117
 *   caducitat 12/20
 *
 *
 * @package Revo\Services
 */
class PaymentGatewayRedsys extends PaymentGateway{

    private $tpv;

    /**
     * @return bool
     */
    public function doesReturnCustomer(){
        return false;
    }
    public static function getConfigFields(){
        return ['merchant', 'currency', 'terminal','secret','secret_test'];
    }

    /**
     * This function sets up the configuration data
     */
    public function setup(){

        $this->tpv = new Tpv();

        $this->tpv->setMerchantcode     ($this->config['merchant']);    //Reemplazar por el código que proporciona el banco
        $this->tpv->setCurrency         ($this->config['currency']);
        $this->tpv->setTransactiontype  ('0');
        $this->tpv->setTerminal         ($this->config['terminal']);
        $this->tpv->setMethod           ('C');          //Solo pago con tarjeta, no mostramos iupay

        $this->tpv->setNotification     ($this->config['notificationURL']); //Url de notificacion
        $this->tpv->setUrlOk            ($this->config['okURL']);           //Url OK
        $this->tpv->setUrlKo            ($this->config['cancelURL']);       //Url KO

        $this->tpv->setVersion          ('HMAC_SHA256_V1');

        if(!$this->test){
            $this->tpv->setEnviroment('live');
        }
        else{
            $this->config['secret'] = $this->config['secret_test'];
            $this->tpv->setEnviroment('test');
        }
    }

    public function payForm($amount, $description, $orderId){

        $key        = $this->config['secret'];
        $orderID    = str_pad($orderId, 4, "0", STR_PAD_LEFT);

        $this->tpv->setAmount($amount);
        $this->tpv->setOrder($orderID);
        $this->tpv->setProductDescription($description);

        $signature = $this->tpv->generateMerchantSignature($key);
        $this->tpv->setMerchantSignature($signature);
        $this->tpv->setNameForm('payment');
        $this->tpv->setIdForm('payment');

        $form = $this->tpv->createForm();

        echo $form;

        return $this;
    }


    /**
     * @return bool if payment is successful or not
     */
    public function checkIPN()
    {
        if($this->tpv == null) {
            $this->tpv = new Tpv();
        }

        $key                = $this->config['secret'];

        $parameters         = $this->tpv->getMerchantParameters($_POST["Ds_MerchantParameters"]);
        $DsResponse         = $parameters["Ds_Response"];
        $DsResponse         += 0;
        $this->orderId      = $parameters['Ds_Order'];

        if ($this->tpv->check($key, $_POST) && $DsResponse <= 99) {
            //Pago correcto
            $this->amount               = $parameters['Ds_Amount']/100;
            return true;

        } else {
            $this->errorMessage = "Redsys says that purchase failed";
            return false;
        }
    }
}