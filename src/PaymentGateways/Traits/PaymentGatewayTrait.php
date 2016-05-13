<?php namespace BadChoice\Panama\PaymentGateways\Traits;

use BadChoice\Panama\PaymentGateways\PaymentGateway;
use Illuminate\Http\Request;
trait PaymentGatewayTrait {

    //==================================================================
    // PUBLIC to use out
    //==================================================================
    /**
     * @param Request $request the request so it can create the Return URL /okUrl and cancelURL automatically for each website
     * @return \BadChoice\Panama\PaymentGateways\PaymentGatewayPaypal|\BadChoice\Panama\PaymentGateways\PaymentGatewayRedsys|null
     */
    public function provider(Request $request = null){
        return PaymentGateway::createNew($this->type,
                                         $this->test,
                                         $this->getConfigArray($request)
        );
    }

    public function typeName(){
        return \BadChoice\Panama\PaymentGateways\PaymentGateway::provides()[$this->type];
    }


    //==================================================================
    // HELPERS
    //==================================================================
    private function parseConfig($attributes = null){
        $config       = [];
        $configFields = \BadChoice\Panama\PaymentGateways\PaymentGateway::configFieldsFor($this->type);
        foreach($configFields as $field){
            if($attributes) { $config[$field] = $attributes[$field]; }
            else            { $config[$field] = $this->$field;       }
            unset($this->$field);
        }
        $this->config = json_encode($config);
    }

    public function getConfigArray($request){
        return array_merge((array)($this->config), $this->getUrls($request,'checkout'));
    }

    private function getUrls($request, $route){
        $root = $request ? $request->root() : '';
        return [
            "notificationURL"   => $root . "/".$route."/ipn/". $this->id,
            "okURL"             => $root . "/".$route."/ok",
            "cancelURL"         => $root . "/".$route."/cancel",
        ];
    }


    //==================================================================
    // OVERRIDES
    //==================================================================
    public function save(array $options = []){
        $this->parseConfig();
        return parent::save($options);
    }

    public function update(array $attributes = [], array $options = []){
        $this->parseConfig($attributes);
        return parent::update($attributes,$options);
    }
    public function getConfigAttribute($value){
        return json_decode($value);
    }
    public function __get($property) {
        if(array_key_exists('config',$this->attributes)){
            if( in_array($property,\BadChoice\Panama\PaymentGateways\PaymentGateway::configFieldsFor($this->attributes['type'])) ){
                return json_decode($this->attributes['config'])->$property;
            }
        }
        return parent::__get($property);
    }
}
