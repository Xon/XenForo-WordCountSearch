<?php

// This class is used to encapsulate global state between layers without using $GLOBAL[] or
// relying on the consumer being loaded correctly by the dynamic class autoloader
class SV_WordCountSearch_Globals
{
    const WordCountField = 'word_count';

    private function __construct() {}
}
