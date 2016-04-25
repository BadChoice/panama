<?php namespace BadChoice\Panama;

class CartContent{
    public $cart_id;        // Unique cart id to find the content
    public $id;
    public $price;          // PVP
    public $quantity;
    public $name;
    public $options;
    public $taxPercentage;  // Included in price

    public function __construct(array $content)
    {
        if(!isset($content['id']))      { throw new \Exception("Id is required");   }
        if(!isset($content['name']))    { throw new \Exception("Name is required"); }

        $this->id       = $content['id'];
        $this->name     = $content['name'];

        $this->cart_id  = isset($content['cart_id'])    ? $content['cart_id']:uniqid();
        $this->price    = isset($content['price'])      ? $content['price']:0;
        $this->quantity = isset($content['quantity'])   ? $content['price']:1;
        $this->tax      = isset($content['tax'])        ? $content['tax']:0;
        $this->options  = isset($content['$options'])   ? $content['$options']:collect([]);
    }

    public function update($updateArray){
        foreach($updateArray as $key => $value){
            $this->$key = $value;
        }
    }

    public function isEqual($content){
        if($this->id        != $content->id)      return false;
        if($this->name      != $content->name)    return false;
        if($this->price     != $content->price)   return false;
        if($this->tax       != $content->tax)     return false;
        if($this->options   != $content->options) return false;
        return true;
    }

    public function individualPrice(){
        $individualPrice = $this->price;
        foreach ($this->options as $option) {
            $individualPrice += $option->price;
        }
        return $individualPrice;
    }

    public function total(){
        return $this->individualPrice() * $this->quantity;
    }

}