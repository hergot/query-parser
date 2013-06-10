<?php

namespace hergot\queryParser;

class Expression {
    private $operator = NULL;
    private $operands = array();
    
    public function __construct($operator, array $operands=array()) {
        $this->operands = $operands;
        $this->operator = $operator;
    }
    
    public function getOperator() {
        return $this->operator;
    }
    
    public function getOperands() {
        return $this->operands;
    }
}