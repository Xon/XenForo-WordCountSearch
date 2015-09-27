<?php

class SV_SearchImprovements_Listener
{
    const AddonNameSpace = 'SV_SearchImprovements_';

    public static function load_class($class, array &$extend)
    {
        $extend[] = self::AddonNameSpace.$class;
    }
}