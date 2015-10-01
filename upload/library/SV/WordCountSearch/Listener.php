<?php

class SV_WordCountSearch_Listener
{
    const AddonNameSpace = 'SV_WordCountSearch_';

    public static function load_class($class, array &$extend)
    {
        $extend[] = self::AddonNameSpace.$class;
    }
}