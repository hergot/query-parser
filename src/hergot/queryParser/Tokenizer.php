<?php

namespace hergot\queryParser;

class Tokenizer {
    
    private $tokens = array();
    
    public function __construct() {
        $this->initTokens();
    }
    
    public function tokenize($string) {
        $splitMask = $this->buildSplitMask();
        $rawTokens = preg_split($splitMask, $string, -1, PREG_SPLIT_NO_EMPTY | PREG_SPLIT_DELIM_CAPTURE);
        $tokens = array_filter($rawTokens, function($item) {
            return trim($item[0]) !== '';
        });
        $classifiedTokens = array_map(function($item) {
            $class = $this->classify($item);
            if ($class === 'string') {
                $quote = $item[0];
                $item = str_replace($quote . $quote, $quote, trim($item, $quote));
            }
            return array('class' => $class, 'token' => $item);
        }, $tokens);
        return $classifiedTokens;
    }
        
    private function initTokens() {
        $this->tokens['operator'] = array('+', '-', '*', '/', '||', 'and', 'or', 'in', '=', '!=', '>', '>=', '<', '<=');
        $this->tokens['brace'] = array('(', ')', '[', ']');
        $this->tokens['whitespace'] = array("\n", "\r", "\t", ' ');
        $this->tokens['quote'] = array('\'', '"');
        $this->tokens['separator'] = array(',');
    }
    
    private function buildSplitMask() {
        $maskParts = array();
        foreach ($this->tokens as $values) {
            $escapedValues = array_map(function($item) { 
                if ($item === "\n") {
                    return '\n';
                } elseif ($item === "\r") {
                    return '\r';
                } elseif ($item === "\t") {
                    return '\t';
                } else {
                    return preg_quote($item, '@');                 
                }
            }, $values);
            $maskParts[] = '(' . implode('|', $escapedValues) . ')';
        }
        return '@(".*(?<!")")|(\'.*(?<!\')\')|' . implode('|', $maskParts) . '@';
    }
    
    private function classify($token) {
        if (is_numeric($token)) {
            return 'numeric';
        }
        
        if ($token[0] === '\'' || $token[0] === '"') {
            return 'string';
        }
        
        foreach ($this->tokens as $class => $values) {
            if (in_array($token, $values)) {
                return $class;
            }
        }
    }
    
}