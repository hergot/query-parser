<?php
/**
 * Token element
 *
 * PHP Version 5
 *
 * @category Element
 * @package  QueryParser
 * @author   Milan Hradil <milan.hradil@email.cz>
 */
namespace hergot\queryParser;

/**
 * Token class encapsulate query elements - tokens
 *
 * @category Element
 * @package  QueryParser
 * @author   Milan Hradil <milan.hradil@email.cz>
 */
class Token {

    /**
     * @var string
     */
    private $content;

    /**
     * @var string|NULL
     */
    private $class;

    /**
     * Class constructor
     *
     * @param string $content
     * @param string|NULL $class
     */
    public function __construct($content, $class = null)
    {
        $this->content = $content;
        $this->class = $class;
    }

    /**
     * Retrieve token content
     *
     * @return string
     */
    public function getContent()
    {
        return $this->content;
    }

    /**
     * Retrieve token class
     *
     * @return string|NULL
     */
    public function getClass()
    {
        return $this->class;
    }
}