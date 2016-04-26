<?php namespace BadChoice\Panama;
use Session;
class Cart{
    protected   $session_cart_name = 'panama_cart';
    private     $contents;

    public function __construct()
    {
        $this->contents = collect(Session::get($this->session_cart_name));
    }

    public function add(array $newContent){
        $newContent = new CartContent($newContent);
        foreach($this->contents as $content){
            if($content->isEqual($newContent)){
                $content->update(["quantity" => $content->quantity + $newContent->quantity]);
                $this->saveCart();
                return $content;
            }
        }
        $this->contents->push($newContent);
        $this->saveCart();
        return $newContent;
    }

    public function update($cart_id, array $updateArray){
        $content =  $this->contents->get(
            $this->getContentKey($cart_id)
        );
        $content->update($updateArray);
        $this->saveCart();
    }

    public function remove($cart_id){
        $this->contents = $this->contents->except(
            $this->getContentKey($cart_id)
        );
        $this->saveCart();
    }

    public function content($cart_id){
        return $this->contents->get(
            $this->getContentKey($cart_id)
        );
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

    public function count(){
        $count = 0;
        foreach($this->contents as $content){
            $count += $content->quantity;
        }
        return $count;
    }

    public function destroy(){
        Session::forget($this->session_cart_name);
    }

    //=============================================================================
    // PRIVATE
    //=============================================================================
    private function getContentKey($cart_id){
        return $this->contents->search(function ($content, $key) use (&$cart_id) {
            return $content->cart_id == $cart_id;
        });
    }
    private function saveCart(){
        Session::put($this->session_cart_name , $this->contents->toArray());
    }
}