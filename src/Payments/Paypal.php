<? namespace BadChoice\Panama\Payments;

use Config;

/**
 * Class Paypal
 *
 * This class helps you create payments through paypal
 *
 *    ## Configuration values
 *    The configuration values need to be set in config/services.php
 *
 *     'paypal' => [
 *          'business'      => 'xxx@xxx.xx',
 *          'ipnURL'        => 'xxx',    //The silent url that will be called when payment completed (can be ok or not)
 *          'returnURL'     => 'xxx', //The silent url that will be called when payment completed (can be ok or not)
 *          'cancelURL'     => 'xxx', //The silent url that will be called when payment completed (can be ok or not)
 *    ]
 *
 *    ## Usage
 *    To create a payment from a controller you can simply
 *    `return Paypal::createForm(true,$amount, "REVO " . $points." Points", "1235", $points)->redirect();`
 *
 *    It will redirect automatically to the bank pay page
 *
 *    If you want to show the payment button you can simply
 *    {!! Paypal::createForm(true,$amount, "REVO " . $points." Points", "1235", $points) !!}
 *
 *    The bank will do a silent call to `merchantUrl` and in that route you can do
 *    ```
 *    if(Paypal::isCompletedPaymentSuccessful()){
 *
 *    }
 *    else{
 *
 *    }
 *    ```
 *
 * @package Revo\Services
 */
class Paypal extends BasePayService{

    const REAL_URL = "https://www.paypal.com/cgi-bin/webscr";
    const TEST_URL = "https://www.sandbox.paypal.com/cgi-bin/webscr";


    // Configuration values
    private $business;
    private $ipnURL;
    private $returnURL;
    private $cancelURL;



    /**
     * @return bool
     */
    public function doesReturnCustomer(){
        return true;
    }

    /**
     * This function sets up the configuration data
     */
    public function setup(){

        if($this->config == null) {
            $this->business = Config::get('services.paypal.business');
        }
        else{
            $this->business = $this->config['business'];
        }

        $this->ipnURL           = Config::get('services.paypal.ipnURL');
        $this->returnURL        = Config::get('services.paypal.returnURL');
        $this->cancelURL        = Config::get('services.paypal.cancelURL');

        $this->urlPayment       = Paypal::REAL_URL;

        if ($this->test) {
            $this->urlPayment   = Paypal::TEST_URL;
        }
    }

    /**
     * Prints the form with the submitt button that will open the Banc Sabadell
     * payment page
     *
     * @param amount amount to charge in cents (20,00€ => 2000)
     * @param description description of what is bought
     * @param orderId unique order id value of the payment, if payment failed this id can't be reused
     * @param merchantData (optional) data that we want to receive on the URL notification
     * @return the paypal class so it can be chained
     */
    public function payForm($amount, $description, $orderId){

        $this->orderId          = str_pad($orderId, 4, "0", STR_PAD_LEFT);

        echo '<form id="payment" action="'. $this->urlPayment.'" method="post">';
        echo '<input type="hidden" name="cmd"             value="_xclick">';
        echo '<input type="hidden" name="business"        value="'.$this->business.'">';
        echo '<input type="hidden" name="item_name"       value="'.$description.'">';
        echo '<input type="hidden" name="currency_code"   value="EUR">';
        echo '<input type="hidden" name="amount"          value="'.$amount.'">';

        echo '<input type="hidden" name="return"          value="'.$this->returnURL.'">';
        echo '<input type="hidden" name="cancel_return"   value="'.$this->cancelURL.'">';

        echo '<input type="hidden" name="invoice"       id="invoice"    value="'.$this->orderId.'" >';
        echo '<input type="hidden" name="notify_url"    id="notify_url" value="'.$this->ipnURL.'"/>';

        echo '<input type="hidden" name="no_shipping" id="no_shipping" value=2 />'; // 0 promp for shipping but do not require, 1 do not promp, 2 prompt and require

        echo '<button type="submit"><i class="fa fa-paypal"></i> BUY</button>';

        echo '</form>';

        return $this;
    }


    /**
     * @return bool if payment is successful or not
     */
    public function checkIPN()
    {

        $this->orderId      = $_POST['invoice'];
        define("DEBUG", 0);         // Set to 0 once you're ready to go live
        define("USE_SANDBOX", $this->test);
        define("LOG_FILE",  storage_path()."/logs/ipn.log");

        // Read POST data
        // reading posted data directly from $_POST causes serialization
        // issues with array data in POST. Reading raw POST data from input stream instead.
        $raw_post_data = file_get_contents('php://input');
        $raw_post_array = explode('&', $raw_post_data);
        $myPost = array();

        foreach ($raw_post_array as $keyval) {
            $keyval = explode('=', $keyval);
            if (count($keyval) == 2)
                $myPost[$keyval[0]] = urldecode($keyval[1]);
        }

        // read the post from PayPal system and add 'cmd'
        $req = 'cmd=_notify-validate';
        if (function_exists('get_magic_quotes_gpc')) {
            $get_magic_quotes_exists = true;
        }
        foreach ($myPost as $key => $value) {
            if ($get_magic_quotes_exists == true && get_magic_quotes_gpc() == 1) {
                $value = urlencode(stripslashes($value));
            } else {
                $value = urlencode($value);
            }
            $req .= "&$key=$value";
        }

        // Post IPN data back to PayPal to validate the IPN data is genuine
        // Without this step anyone can fake IPN data
        if (USE_SANDBOX == true) {
            $paypal_url = "https://www.sandbox.paypal.com/cgi-bin/webscr";
        } else {
            $paypal_url = "https://www.paypal.com/cgi-bin/webscr";
        }
        $ch = curl_init($paypal_url);
        if ($ch == FALSE) {
            $this->errorMessage = "Can't connect to paypal";
            return FALSE;
        }
        curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $req);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
        curl_setopt($ch, CURLOPT_FORBID_REUSE, 1);

        if (DEBUG == true) {
            curl_setopt($ch, CURLOPT_HEADER, 1);
            curl_setopt($ch, CURLINFO_HEADER_OUT, 1);
        }
        // CONFIG: Optional proxy configuration
        //curl_setopt($ch, CURLOPT_PROXY, $proxy);
        //curl_setopt($ch, CURLOPT_HTTPPROXYTUNNEL, 1);
        // Set TCP timeout to 30 seconds
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 30);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Connection: Close'));

        // CONFIG: Please download 'cacert.pem' from "http://curl.haxx.se/docs/caextract.html" and set the directory path
        // of the certificate as shown below. Ensure the file is readable by the webserver.
        // This is mandatory for some environments.
        //$cert = __DIR__ . "./cacert.pem";
        //curl_setopt($ch, CURLOPT_CAINFO, $cert);
        $res = curl_exec($ch);
        if (curl_errno($ch) != 0) // cURL error
        {
            if (DEBUG == true) {
                error_log(date('[Y-m-d H:i e] ') . "Can't connect to PayPal to validate IPN message: " . curl_error($ch) . PHP_EOL, 3, LOG_FILE);
            }
            curl_close($ch);
            $this->errorMessage = "Can't connect to paypal";
            return false;
            exit;
        } else {
            // Log the entire HTTP response if debug is switched on.
            if (DEBUG == true) {
                error_log(date('[Y-m-d H:i e] ') . "HTTP request of validation request:" . curl_getinfo($ch, CURLINFO_HEADER_OUT) . " for IPN payload: $req" . PHP_EOL, 3, LOG_FILE);
                error_log(date('[Y-m-d H:i e] ') . "HTTP response of validation request: $res" . PHP_EOL, 3, LOG_FILE);
            }
            curl_close($ch);
        }
        // Inspect IPN validation result and act accordingly
        // Split response headers and payload, a better way for strcmp
        $tokens = explode("\r\n\r\n", trim($res));
        $res = trim(end($tokens));
        if (strcmp($res, "VERIFIED") == 0) {
            // check whether the payment_status is Completed
            // check that txn_id has not been previously processed
            // check that receiver_email is your PayPal email
            // check that payment_amount/payment_currency are correct
            // process payment and mark item as paid.
            // assign posted variables to local variables
            //$item_name = $_POST['item_name'];
            //$item_number = $_POST['item_number'];
            //$payment_status = $_POST['payment_status'];
            //$payment_amount = $_POST['mc_gross'];
            //$payment_currency = $_POST['mc_currency'];
            //$txn_id = $_POST['txn_id'];
            //$receiver_email = $_POST['receiver_email'];
            //$payer_email = $_POST['payer_email'];

            if($_POST['payment_status'] == 'Completed') {

                $this->amount               = $_POST['mc_gross'];
                $this->orderId              = $_POST['invoice'];
                $this->customer_address     = $_POST['address_street'];
                $this->customer_postal_code = $_POST['address_zip'];
                $this->customer_city        = $_POST['address_city'];
                $this->customer_country     = $_POST['address_country'];
                $this->customer_name        = $_POST['first_name'];
                $this->customer_lastName    = $_POST['last_name'];
                $this->customer_email       = $_POST['payer_email'];

                if (DEBUG == true) {
                    error_log(date('[Y-m-d H:i e] ') . "Verified IPN: $req " . PHP_EOL, 3, LOG_FILE);
                }

                return true;
            }
            else{
                $this->errorMessage = "Status not completed:" . $_POST['payment_status'];
                return false;
            }

        } else if (strcmp($res, "INVALID") == 0) {
            // log for manual investigation
            // Add business logic here which deals with invalid IPN messages
            if (DEBUG == true) {
                error_log(date('[Y-m-d H:i e] ') . "Invalid IPN: $req" . PHP_EOL, 3, LOG_FILE);
            }

            $this->errorMessage = "Invalid paypal response";
            return false;
        }
    }
}