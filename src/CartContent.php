<?php namespace BadChoice\Panama;

class CartContent{
    public $cart_id;        // Unique cart id to find the content
    public $id;
    public $price;          // PVP
    public $quantity;
    public $name;
    public $options;
    public $taxPercentage;  // Included in price

    /**
     * Custom user data, it needs to be a collection and if it has the variable price it will be added
     * to the content price
     * @var
     */
    public $userData;       // TODO: Use options instead?

    private $model;
    private $modelNamespace;

    public function __construct(array $content)
    {
        if(!isset($content['id']))      { throw new \Exception("Id is required");   }
        if(!isset($content['name']))    { throw new \Exception("Name is required"); }

        $this->id           = $content['id'];
        $this->name         = $content['name'];

        $this->cart_id      = isset($content['cart_id'])    ? $content['cart_id']:uniqid();
        $this->price        = isset($content['price'])      ? $content['price']:0;
        $this->quantity     = isset($content['quantity'])   ? $content['price']:1;
        $this->tax          = isset($content['tax'])        ? $content['tax']:0;
        $this->options      = isset($content['$options'])   ? $content['$options']:collect([]);
        $this->userData     = isset($content['userData'])   ? $content['userData']:collect([]);
    }

    public function update($updateArray){
        foreach($updateArray as $key => $value){
            $this->$key = $value;
        }
    }

    public function isEqual($content){
        if($this->id            != $content->id)            return false;
        if($this->name          != $content->name)          return false;
        if($this->price         != $content->price)         return false;
        if($this->tax           != $content->tax)           return false;
        if($this->options       != $content->options)       return false;
        if($this->menuContents  != $content->menuContents)  return false;
        return true;
    }

    public function individualPrice(){
        $individualPrice = $this->price;
        foreach ($this->options as $option) {
            $individualPrice += $option->price;
        }
        foreach ($this->userData as $userData){
            if(isset($userData->price))
                $individualPrice += $userData->price;
        }
        return $individualPrice;
    }

    public function total(){
        return $this->individualPrice() * $this->quantity;
    }

    public function associate($model, $modelNamespace = ""){
        if( ! class_exists($modelNamespace . '\\' . $model)) throw new \Exception("Model doesn't exists");
        $this->model            = $model;
        $this->modelNamespace   = $modelNamespace;
        return $this;
    }

    public function __get($arg)
    {
        /*if($this->has($arg))
        {
            return $this->get($arg);
        }*/
        if($arg == lcfirst($this->model))
        {
            $modelInstance = $this->modelNamespace ? $this->modelNamespace . '\\' .$this->model: $this->model;
            $model = new $modelInstance;
            return $model->find($this->id);
        }
        return null;
    }

}