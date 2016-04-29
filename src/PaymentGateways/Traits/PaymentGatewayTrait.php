<?php namespace BadChoice\Panama\PaymentGateways\Traits;

trait PaymentGatewayTrait {

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
        return [
            "notificationURL"   => $request->root() . "/".$route."/ipn/". $this->type,
            "okURL"             => $request->root() . "/".$route."/ok",
            "cancelURL"         => $request->root() . "/".$route."/cancel",
        ];
    }

    public function typeName(){
        return \BadChoice\Panama\PaymentGateways\PaymentGateway::provides()[$this->type];
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
