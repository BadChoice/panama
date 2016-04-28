<?php namespace BadChoice\Panama\PaymentGatewayTrait;

trait PaymentGatewayTrait {

    public function save(array $options = [])
    {
        $config = [];
        $configFields = \BadChoice\Panama\Payments\PaymentGateway::configFieldsFor($this->type);
        foreach($configFields as $field){
            $config[$field] = $this->$field;
            unset($this->$field);
        }
        $this->config = json_encode($config);

        return parent::save($options);
    }
}
