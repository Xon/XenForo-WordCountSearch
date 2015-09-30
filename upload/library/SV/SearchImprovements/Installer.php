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

        $requireIndexing = array();
        if ($version == 0)
        {
            $requireIndexing['post'] = true;
        }
        
        $db = XenForo_Application::getDb();
        
        $db->query("
            CREATE TABLE IF NOT EXISTS `xf_post_words`
            (
                `post_id` int(10) unsigned NOT NULL,
                `word_count` int(10) unsigned NOT NULL,
                PRIMARY KEY (`post_id`)
            ) ENGINE=InnoDB CHARACTER SET utf8 COLLATE utf8_general_ci
        ");

        // if Elastic Search is installed, determine if we need to push optimized mappings for the search types
        SV_Utils_Install::updateXenEsMapping($requireIndexing, array(
            'post' => array(
                "properties" => array(
                    "word_count" => array("type" => "long", "store" => "yes"),
                )
            )
        ));
    }

    public static function uninstall()
    {
        $db = XenForo_Application::getDb();

        $db->query("
            DROP TABLE IF EXISTS `xf_post_words`
        ");
    }
}