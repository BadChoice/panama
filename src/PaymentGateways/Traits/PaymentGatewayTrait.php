<?php namespace BadChoice\Panama\PaymentGateways\Traits;

trait PaymentGatewayTrait {

    public function save(array $options = [])
    {
        $config = [];
        $configFields = \BadChoice\Panama\PaymentGateways\PaymentGateway::configFieldsFor($this->type);
        foreach($configFields as $field){
            $config[$field] = $this->$field;
            unset($this->$field);
        }
        $this->config = json_encode($config);

        return parent::save($options);
    }
}
