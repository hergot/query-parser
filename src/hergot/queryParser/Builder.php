<?php

namespace hergot\queryParser;

class Builder {
    /**
     * 
     * @param array $tokens
     * @return \hergot\queryParser\Expression|array
     * @throws Exception
     */
    public function build(array $tokens) {
        $operands = array();
        $operator = null;
        $operatorIndex = null;
        for ($i = 0, $length = count($tokens); $i < $length; $i++) {
            /* @var $token Token */
            $token = $tokens[$i];
            if ($token->getClass() === 'brace' && $token->getContent() === '(') {
                $buffer = array();
                $deep = 1;
                for ($j = $i + 1; $j < $length; $j++) {
                    if ($tokens[$j]->getClass() === 'brace') {
                        if ($tokens[$j]->getContent() === '(') {
                            $deep++;
                        } elseif ($tokens[$j]->getContent() === ')') {
                            $deep--;
                            if ($deep === 0) {                                
                                break;
                            }
                        }
                    }
                    if ($j === $length && $tokens[$length - 1]->getContent() !== ')') {
                        throw new Exception('Unclosed brace');
                    }
                    $buffer[] = $tokens[$j];
                }
                $operands[] = $this->build($buffer);
                $i = $j;
            } elseif ($token->getClass() === 'brace' && $token->getContent() === '[') {
                $buffer = array();
                $deep = 1;
                for ($j = $i + 1; $j < $length; $j++) {
                    if ($tokens[$j]->getClass() === 'brace') {
                        if ($tokens[$j]->getContent() === '[') {
                            $deep++;
                        } elseif ($tokens[$j]->getContent() === ']') {
                            $deep--;
                            if ($deep === 0) {                                
                                break;
                            }
                        }
                    }
                    if ($j === $length && $tokens[$length - 1]->getContent() !== ']') {
                        throw new Exception('Unclosed array brace');
                    }
                    $buffer[] = $tokens[$j];
                }
                $operands[] = $this->buildArrayElement($buffer);
                $i = $j;
            } else {
                if ($token->getClass() === 'operator') {
                    if ($operator === null) {
                        $operator = $token;
                        $operatorIndex = count($operands);
                    } else {
                        if ($operator->getContent() === $token->getContent()) {
                            continue;
                        }
                        if ($this->getOperatorPriority($operator->getContent()) <= $this->getOperatorPriority($token->getContent())) {
                            $operands = array(new Expression($operator->getContent(), $operands));
                            $operator = $token;
                        } else {                            
                            $buffer = array_slice($operands, $operatorIndex);
                            $operands = array_slice($operands, 0, $operatorIndex);
                            for ($j = $i; $j < $length; $j++) {
                                if ($tokens[$j]->getClass() === 'operator' 
                                        && $this->getOperatorPriority($tokens[$j]->getContent()) >= $this->getOperatorPriority($token->getContent())) {
                                    break;
                                }
                                $buffer[] = $tokens[$j];
                            }
                            $i = $j;
                            $operands[] = $this->build($buffer);
                        }                        
                    }
                } else {
                    $operands[] = $token;
                }
            }
        }
        if ($operator !== null) {
            return new Expression($operator->getContent(), $operands);
        } elseif (count($operands) === 1) {
            return $operands[0];
        } else {
            return $operands;
        }
    }
    
    private function getOperatorPriority($operator) {
        if (in_array($operator, array('*', '/', '%'))) {
            return 1;
        }
        if (in_array($operator, array('+', '-', '||'))) {
            return 2;
        }
        if (in_array($operator, array('>', '>=', '<', '<='))) {
            return 3;
        }
        if (in_array($operator, array('=', '!='))) {
            return 4;
        }
        if (in_array($operator, array('in'))) {
            return 5;
        }
        if (in_array($operator, array('and', 'or'))) {
            return 7;
        }
        return 8;
    }
    
    private function buildArrayElement(array $tokens) {
        $chunks = array();
        $buffer = array();
        for ($i = 0, $length = count($tokens); $i < $length; $i++) {
            $token = $tokens[$i];
            if ($token->getClass() === 'separator') {
                if (!empty($buffer)) {
                    $chunks[] = $buffer;
                    $buffer = array();
                }
                continue;
            }
            $buffer[] = $token;
        }
        if (!empty($buffer)) {
            $chunks[] = $buffer;
            $buffer = array();
        }
        foreach ($chunks as $index => $chunk) {
            $chunks[$index] = $this->build($chunk);
        }
        return new ArrayElement($chunks);
    }
}