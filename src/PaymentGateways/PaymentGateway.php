<? namespace BadChoice\Panama\Payments;

abstract class PaymentGateway{

    const PAYPAL = 0;
    const REDSYS = 1;

    public $test;
    public $config;
    public $errorMessage;

    //Return variables
    public      $orderId;
    public      $amount;

    protected   $customer_email;
    protected   $customer_name;
    protected   $customer_lastName;
    protected   $customer_address;
    protected   $customer_city;
    protected   $customer_postal_code;
    protected   $customer_country;

    //==================================================================
    // STATIC Constructor
    //==================================================================
    public static function createNew($type, $test, $config){
        if($type        == static::PAYPAL)  return new PaymentGatewayPaypal($test, $config);
        else if($type   == static::REDSYS)  return new PaymentGatewayRedsys($test, $config);
        else return null;
    }

    public abstract function getConfigFields();

    //==================================================================
    // CONSTRUCTOR
    //==================================================================
    public function __construct($test = false, $config = null){
        $this->test     = $test;
        $this->config   = $config;

        $this->setup();
    }

    /**
     * Redirects to the bank payment page
     * Basically it simulates a submit button click
     */
    public function redirect(){
        echo "<script>";
        echo "document.getElementById('payment').style.display='none';";
        echo "document.getElementById('payment').submit();";
        echo "</script>";
    }

    public function getCustomerData()
    {
        if($this->doesReturnCustomer()){
            return [
                "email"         => $this->customer_email,
                "name"          => $this->customer_name,
                "lastName"      => $this->customer_lastName,
                "address"       => $this->customer_address,
                "city"          => $this->customer_city,
                "postalCode"    => $this->customer_postal_code,
                "country"       => $this->customer_country,
            ];
        }
        else{
            return null;
        }
    }

    //==================================================================
    // ABSTRACT FUNCTIONS
    //==================================================================
    /**
     * Returns true or false if the service returns the customer data such as shipping (paypal does return it, redsys doesn't)
     * @return bool
     */
    public function doesReturnCustomer(){
        return false;
    }

    /**
     * This function sets up the configuration data (some values may be in the config array)
     */
    public function setup(){ }

    /**
    * The form should have 'payment' as id so it can be redirected
    * This should return the $this so it can be chained with redirect
    */
    protected function payForm($amount, $description, $orderId){}

    /**
     * Call this function when receiving the online purchase service notification
     * It should store amount and orderID variables so they can be fetched afterwards
     */
    protected function checkIPN(){}
}