<?php
class WC_Product_External_Custom extends WC_Product_Custom {
    private $WC_Product_External;
    /**
     * Initialize external product.
     *
     * @param mixed $product
     */
    public function __construct( $product ) {
        $this->WC_Product_External = new WC_Product_External($product);
        $this->product_type = 'external';
        parent::__construct( $product );
    }
    // fake "extends WC_Product_External" using magic function
    public function __call($method, $args) {
        if (method_exists($this->WC_Product_External, $method)) {
            $reflection = new ReflectionMethod($this->WC_Product_External, $method);
            if (!$reflection->isPublic()) {
                throw new RuntimeException("Call to not public method ".get_class($this)."::$method()");
            }
            return call_user_func_array(array($this->WC_Product_External, $method), $args);
        } else {
            throw new RuntimeException("Call to undefined method ".get_class($this)."::$method()");
        }
    }
} 