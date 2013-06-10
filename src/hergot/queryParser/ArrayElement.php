<?php

namespace hergot\queryParser;

class ArrayElement {
    
    private $elements;
    
    public function __construct(array $elements) {
        $this->elements = $elements;
    }
    
    public function getElements() {
        return $this->elements;
    }
}