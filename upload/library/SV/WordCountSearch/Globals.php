<?php

// This class is used to encapsulate global state between layers without using $GLOBAL[] or
// relying on the consumer being loaded correctly by the dynamic class autoloader
class SV_WordCountSearch_Globals
{
    public static $SearchController = null;
    public static $wordCountThreshold = 20;

    private function __construct() {}
}
