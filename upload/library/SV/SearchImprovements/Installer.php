<?php

class SV_SearchImprovements_Installer
{
    public static function install($installedAddon, array $addonData, SimpleXMLElement $xml)
    {
        $version = isset($installedAddon['version_id']) ? $installedAddon['version_id'] : 0;

        if (!(XenForo_Application::get('options')->enableElasticsearch) || !($XenEs = XenForo_Model::create('XenES_Model_Elasticsearch')))
        {
            throw new Exception("Require Enhanced Search to be installed and enabled");
        }

        // if Elastic Search is installed, determine if we need to push optimized mappings for the search types
        $mappings = $XenEs->getOptimizableMappings();
        if ($mappings)
        {
            XenForo_Error::debug(var_export($mappings, true));
            XenForo_Error::logException(new Exception("Please optimize mappings, and re-index all content types."));
        }
    }

    public static function uninstall()
    {
        $db = XenForo_Application::getDb();
    }
}