<?php

namespace hergot\queryParser;

class Evaluator {
    
    private $callback;
    
    public function __construct() {
        $this->callback = array($this, 'stringCallback');
    }
    
    public function setCallback(\callable $callback) {
        $this->callback = $callback;
    }
    
    public function evaluate(Expression $expression) {
        $result = null;
        switch ($expression->getOperator()) {
            case '*':
                foreach ($expression->getOperands() as $operand) {
                }
                break;
        }
        return $result;
    }
    
    private function stringCallback($value) {
        return $value;
    }
}