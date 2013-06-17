<?php

require 'vendor/autoload.php';

$t = new \hergot\queryParser\Tokenizer();
$tokens = $t->tokenize('1+(2+3)+4 > 3');
$b = new hergot\queryParser\Builder();
var_dump($b->build($tokens));

