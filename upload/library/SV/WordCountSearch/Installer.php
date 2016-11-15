<?php

class SV_WordCountSearch_Installer
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

        if ($version < 1000703)
        {
            $db->query("truncate table xf_post_words");
            XenForo_Application::defer('SV_WordCountSearch_Deferred_WordCountMigration', array('position' => -1), 'WordCountMigration', true);
        }

        if ($version < 1010000)
        {
            );
            if (self::addOnIsActive('sidaneThreadmarks'))
            {
                XenForo_Application::defer(
                    'SV_WordCountSearch_Deferred_ThreadmarkWordCount',
                    array('position' => -1),
                    'ThreadmarkWordCount',
                    true
                );

                $requireIndexing['thread'] = true;
            }
        }

        // if Elastic Search is installed, determine if we need to push optimized mappings for the search types
        // requires overriding XenES_Model_Elasticsearch
        SV_Utils_Deferred_Search::SchemaUpdates($requireIndexing);
    }

    public static function uninstall()
    {
        $db = XenForo_Application::getDb();

        $db->query("
            DROP TABLE IF EXISTS `xf_post_words`
        ");
    }

    public static function addOnIsActive($addOnId)
    {
        if (XenForo_Application::isRegistered('addOns')) {
            return array_key_exists(
                $addOnId,
                XenForo_Application::get('addOns')
            );
        }

        return false;
    }
}
