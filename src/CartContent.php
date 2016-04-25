<?php namespace BadChoice\Panama;

class CartContent{
    public $id;
    public $price;
    public $name;
    public $options;
    public $tax;

    public function __construct(array $content)
    {
        if(!isset($content['id']))  { throw new \Exception("Id is required"); }
        if(!isset($content['name'])){ throw new \Exception("Name is required"); }
        $this->id       = $content['id'];
        $this->name     =    $content['name'];
        $this->price    = isset($content['price'])?$content['price']:0;
        $this->tax      = isset($content['tax'])?$content['tax']:0;
        $this->options  = isset($content['$options'])?$content['$options']:0;
    }

}