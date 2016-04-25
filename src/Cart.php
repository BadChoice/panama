<?php namespace BadChoice\Panama;
use Session;
class Cart{
    protected   $session_cart_name = 'panama_cart';
    private     $contents;

    public function __construct()
    {
        $this->contents = collect(Session::get($this->session_cart_name));
    }

    public function add(array $content){
        $this->contents->push(new CartContent($content));
        $this->saveCart();
    }

    public function remove($cart_id){

    }
    public function contents(){
        return $this->contents;
    }

    public function total(){
        $total = 0;
        foreach($this->contents() as $content){
            $total += $content->total();
        }
        return $total;
    }

    public function destroy(){
        Session::forget($this->session_cart_name);
    }

    private function saveCart(){
        Session::put($this->session_cart_name , $this->contents->toArray());
    }
}