<?php

namespace hergot\queryParser;

class Token {

    private $content;
    private $class;

    public function __construct($content, $class = null) {
        $this->content = $content;
        $this->class = $class;
    }

    public function getContent() {
        return $this->content;
    }

    public function setContent($content) {
        $this->content = $content;
    }

    public function getClass() {
        return $this->class;
    }

    public function setClass($class) {
        $this->class = $class;
    }
}