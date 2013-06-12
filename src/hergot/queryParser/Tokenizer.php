<?php
/**
 * Tokenizer creates tokens from query string
 *
 * PHP Version 5
 *
 * @category Tokenizer
 * @package  QueryParser
 * @author   Milan Hradil <milan.hradil@email.cz>
 */
namespace hergot\queryParser;

/**
 * Class Tokenizer
 * Tokenize query string into list of tokens
 *
 * @category Tokenizer
 * @package  QueryParser
 * @author   Milan Hradil <milan.hradil@email.cz>
 */
class Tokenizer
{
    /**
     * @var array
     */
    private $tokens = array();

    /**
     * Class constructor
     */
    public function __construct()
    {
        $this->initTokens();
    }

    /**
     * Tokenize string into tokens
     *
     * @param string $string query string
     *
     * @return array
     * @throws \RuntimeException
     */
    public function tokenize($string)
    {
        $splitMask = $this->buildSplitMask();
        $tokens = preg_split(
            $splitMask, $string, -1,
            PREG_SPLIT_NO_EMPTY | PREG_SPLIT_DELIM_CAPTURE
        );
        // handle strings -> join tokens which are inside string
        $stringTokens = $this->processStrings($tokens);

        $filteredTokens = array_values(
            array_filter(
                $stringTokens, function ($item) {
                    return trim($item[0]) !== '';
                }
            )
        );

        $classifiedTokens = array_map(
            function ($item) {
                $class = $this->classify($item);
                if ($class === 'string') {
                    $quote = $item[0];
                    $item = str_replace(
                        $quote . $quote, $quote, trim($item, $quote)
                    );
                }
                return new Token($item, $class);
            }, $filteredTokens
        );
        return $classifiedTokens;
    }

    /**
     * Process strings in tokens
     * Group all tokens between quote tokens
     *
     * @param array $tokens list of string tokens
     *
     * @return array
     * @throws \RuntimeException
     */
    private function processStrings(array $tokens)
    {
        $stringTokens = array();
        $buffer = array();
        $inString = false;
        $stringQuote = null;
        for ($index = 0, $length = count($tokens); $index < $length; $index++) {
            $token = $tokens[$index];
            $isQuote = $token === '"' || $token === '\'';
            if ($isQuote && ($stringQuote === null || $stringQuote === $token)) {
                if ($stringQuote === null) {
                    $stringQuote = $token;
                }
                if (isset($tokens[$index+1]) && $tokens[$index+1] === $stringQuote) {
                    $buffer[] = $stringQuote;
                    $index++;
                } else {
                    $inString = !$inString;
                    if (!$inString) {
                        $buffer[] = $token;
                        $stringTokens[] = implode('', $buffer);
                        $buffer = array();
                        $stringQuote = null;
                    } else {
                        $buffer[] = $stringQuote;
                    }
                }
            } elseif ($inString) {
                $buffer[] = $token;
            } else {
                $stringTokens[] = $token;
            }
        }

        if (!empty($buffer)) {
            throw new \RuntimeException(
                'Unclosed string. Context: ' . implode('', $buffer)
            );
        }
        return $stringTokens;
    }

    /**
     * Initialize tokens
     *
     * @return void
     */
    private function initTokens()
    {
        $this->tokens['operator'] = array(
            '+', '-', '*', '/',
            '||',
            'and', 'or',
            'in',
            '=', '!=', '>', '>=', '<', '<='
        );
        $this->tokens['brace'] = array('(', ')', '[', ']');
        $this->tokens['whitespace'] = array("\n", "\r", "\t", ' ');
        $this->tokens['quote'] = array('\'', '"');
        $this->tokens['separator'] = array(',');
        $this->tokens['quote'] = array('"', '\'');
    }

    /**
     * Build regex mask for preg_split
     *
     * @return string
     */
    private function buildSplitMask()
    {
        $maskParts = array();
        foreach ($this->tokens as $values) {
            $escapedValues = array_map(
                function ($item) {
                    if ($item === "\n") {
                        return '\n';
                    } elseif ($item === "\r") {
                        return '\r';
                    } elseif ($item === "\t") {
                        return '\t';
                    } else {
                        return preg_quote($item, '@');
                    }
                }, $values
            );
            $maskParts[] = '(' . implode('|', $escapedValues) . ')';
        }
        return '@' . implode('|', $maskParts) . '@s';
    }

    /**
     * Classify token
     *
     * @param string $token token string
     *
     * @return string|null
     */
    private function classify($token)
    {
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