<?php
/**
 * Array element represents array
 *
 * PHP Version 5
 *
 * @category Element
 * @package  QueryParser
 * @author   Milan Hradil <milan.hradil@email.cz>
 */

namespace hergot\queryParser;

/**
 * Class ArrayElement encapsulate array
 *
 * @category Element
 * @package  QueryParser
 * @author   Milan Hradil <milan.hradil@email.cz>
 */
class ArrayElement
{
    /**
     * @var array
     */
    private $elements;

    /**
     * Class constructor
     *
     * @param array $elements list of elements
     */
    public function __construct(array $elements)
    {
        $this->elements = $elements;
    }

    /**
     * Retrieve list of elements
     *
     * @return array
     */
    public function getElements()
    {
        return $this->elements;
    }
}