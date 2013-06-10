<?php
/**
 * Builder takes array of tokens and build expression
 *
 * PHP Version 5
 *
 * @category Builder
 * @package  QueryParser
 * @author   Milan Hradil <milan.hradil@email.cz>
 */
namespace hergot\queryParser;

/**
 * Class Builder
 * Encapsulate building expression from tokens
 *
 * @category Builder
 * @package  QueryParser
 * @author   Milan Hradil <milan.hradil@email.cz>
 */
class Builder
{
    /**
     * Build expression from tokens
     *
     * @param array $tokens list of Token objects
     *
     * @return \hergot\queryParser\Expression|array
     * @throws Exception
     */
    public function build(array $tokens)
    {
        $operands = array();
        $operator = null;
        $operatorIndex = null;
        for ($i = 0, $length = count($tokens); $i < $length; $i++) {
            /* @var $token Token */
            $token = $tokens[$i];
            if ($token->getClass() === 'brace') {
                if ($token->getContent() === '(') {
                    $pos = $this->findMatchingTokenIndex($tokens, '(', ')', $i + 1);
                    $buffer = array_slice($tokens, $i + 1, $pos - $i);
                    $i = $pos + 1;
                    $operands[] = $this->build($buffer);
                } elseif ($token->getContent() === '[') {
                    $pos = $this->findMatchingTokenIndex($tokens, '[', ']', $i + 1);
                    $buffer = array_slice($tokens, $i + 1, $pos - $i);
                    $i = $pos + 1;
                    $operands[] = $this->buildArrayElement($buffer);
                }
            } elseif ($token->getClass() === 'operator') {
                if ($operator === null) {
                    $operator = $token;
                    $operatorIndex = count($operands);
                } else {
                    if ($operator->getContent() === $token->getContent()) {
                        continue;
                    }
                    $operatorPriority = $this->getOperatorPriority($operator);
                    $tokenPriority = $this->getOperatorPriority($token);
                    if ($operatorPriority <= $tokenPriority) {
                        $operands = array(
                            new Expression($operator, $operands)
                        );
                        $operator = $token;
                    } else {
                        $buffer = array_slice($operands, $operatorIndex);
                        $operands = array_slice($operands, 0, $operatorIndex);
                        $tokenPriority = $this->getOperatorPriority($token);
                        for ($j = $i; $j < $length; $j++) {
                            if ($tokens[$j]->getClass() === 'operator') {
                                $priority = $this->getOperatorPriority($tokens[$j]);
                                if ($priority >= $tokenPriority) {
                                    break;
                                }
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
        if ($operator !== null) {
            return new Expression($operator->getContent(), $operands);
        } elseif (count($operands) === 1) {
            return $operands[0];
        } else {
            return $operands;
        }
    }

    /**
     * Find matching token index in list of tokens
     *
     * @param array  $tokens            list of Token
     * @param string $openTokenContent  open token value
     * @param string $closeTokenContent close token value
     * @param int    $startIndex        starting index in $tokens
     *
     * @return int
     *
     * @throws \RuntimeException
     */
    private function findMatchingTokenIndex(array $tokens, $openTokenContent,
        $closeTokenContent, $startIndex
    ) {
        $deep = 1;
        for ($i = $startIndex, $length = count($tokens); $i < $length; $i++) {
            if ($tokens[$i]->getContent() === $openTokenContent) {
                $deep++;
            } elseif ($tokens[$i]->getContent() === $closeTokenContent) {
                $deep--;
                if ($deep === 0) {
                    break;
                }
            }
            if ($i === $length
                && $tokens[$length - 1]->getContent() !== $closeTokenContent
            ) {
                throw new \RuntimeException('Unclosed');
            }
        }
        return $i - 1;
    }

    /**
     * Get numeric operator priority
     *
     * @param \hergot\queryParser\Token $operator text operator representation
     *
     * @return int
     */
    private function getOperatorPriority(Token $operator)
    {
        $operatorContent = $operator->getContent();
        $result = 7;
        if (in_array($operatorContent, array('*', '/', '%'))) {
            $result = 1;
        } elseif (in_array($operatorContent, array('+', '-', '||'))) {
            $result = 2;
        } elseif (in_array($operatorContent, array('>', '>=', '<', '<='))) {
            $result = 3;
        } elseif (in_array($operatorContent, array('=', '!='))) {
            $result = 4;
        } elseif (in_array($operatorContent, array('in'))) {
            $result = 5;
        } elseif (in_array($operatorContent, array('and', 'or'))) {
            $result = 6;
        }
        return $result;
    }

    /**
     * Build array element from token
     *
     * @param array $tokens list of Token objects
     *
     * @return \hergot\queryParser\ArrayElement
     */
    private function buildArrayElement(array $tokens)
    {
        $chunks = array();
        $buffer = array();
        for ($i = 0, $length = count($tokens); $i < $length; $i++) {
            $token = $tokens[$i];
            if ($token->getClass() === 'separator') {
                if (!empty($buffer)) {
                    $chunks[] = $buffer;
                    $buffer = array();
                }
            } else {
                $buffer[] = $token;
            }
        }
        if (!empty($buffer)) {
            $chunks[] = $buffer;
        }
        foreach ($chunks as $index => $chunk) {
            if (count($chunk) > 1) {
                $chunks[$index] = $this->build($chunk);
            } else {
                $chunks[$index] = $chunk;
            }
        }
        return new ArrayElement($chunks);
    }
}