<?php
class WC_Product_Simple_Custom extends WC_Product_Custom {
    private $WC_Product_Simple;
    /**
     * Initialize simple product.
     *
     * @param mixed $product
     */
    public function __construct( $product ) {
        $this->WC_Product_Simple = new WC_Product_Simple($product);
        $this->product_type = 'simple';
        parent::__construct( $product );
    }
    // fake "extends WC_Product_Simple" using magic function
    public function __call($method, $args) {
        if (method_exists($this->WC_Product_Simple, $method)) {
            $reflection = new ReflectionMethod($this->WC_Product_Simple, $method);
            if (!$reflection->isPublic()) {
                throw new RuntimeException("Call to not public method ".get_class($this)."::$method()");
            }
            return call_user_func_array(array($this->WC_Product_Simple, $method), $args);
        } else {
            throw new RuntimeException("Call to undefined method ".get_class($this)."::$method()");
        }
    }
} 