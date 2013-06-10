<?php
/**
 * Expression represents operator with operands
 *
 * PHP Version 5
 *
 * @category Expression
 * @package  QueryParser
 * @author   Milan Hradil <milan.hradil@email.cz>
 */
namespace hergot\queryParser;

/**
 * Class Expression
 * Encapsulate operator with operands
 *
 * @category Expression
 * @package  QueryParser
 * @author   Milan Hradil <milan.hradil@email.cz>
 */

class Expression
{

    /**
     * @var string|null
     */
    private $operator = null;

    /**
     * @var array
     */
    private $operands = array();

    /**
     * Class constructor
     *
     * @param string $operator operator string value
     * @param array  $operands list of tokens/operands
     */
    public function __construct($operator, array $operands=array())
    {
        $this->operands = $operands;
        $this->operator = $operator;
    }

    /**
     * Retrieve operator
     *
     * @return string
     */
    public function getOperator()
    {
        return $this->operator;
    }

    /**
     * Retrieve operands
     *
     * @return array of Token
     */
    public function getOperands()
    {
        return $this->operands;
    }
}