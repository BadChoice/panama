<?php namespace BadChoice\Panama;
use Session;
class Cart{
    protected   $session_cart_name = 'panama_cart';
    private     $contents;

    public function __construct()
    {
        $this->contents = Session::get($this->session_cart_name);
    }

    public function sayHello(){
        return "Hello!";
    }

    public function add(array $content){
        $this->contents[] = new CartContent($content);
        $this->saveCart();
    }

    public function contents(){
        return $this->contents;
    }

    public function destroy(){
        Session::forget($this->session_cart_name);
    }

    private function saveCart(){
        Session::put($this->session_cart_name , $this->contents);
    }
}