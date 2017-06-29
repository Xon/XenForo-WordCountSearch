<?php

class SV_WordCountSearch_Listener
{
    public static function load_class($class, array &$extend)
    {
        $extend[] = 'SV_WordCountSearch_' . $class;
    }
}