<?php

class SV_WordCountSearch_Installer
{
    public static function install($installedAddon, array $addonData, SimpleXMLElement $xml)
    {
        $version = isset($installedAddon['version_id']) ? $installedAddon['version_id'] : 0;
        $required = '5.4.0';
        $phpversion = phpversion();
        if (version_compare($phpversion, $required, '<'))
        {
            throw new XenForo_Exception("PHP {$required} or newer is required. {$phpversion} does not meet this requirement. Please ask your host to upgrade PHP", true);
        }
        if (XenForo_Application::$versionId < 1030070)
        {
            throw new XenForo_Exception('XenForo 1.3.0+ is Required!', true); // Make this show nicely.
        }
        if (!(XenForo_Application::get('options')->enableElasticsearch) || !($XenEs = XenForo_Model::create('XenES_Model_Elasticsearch')))
        {
            throw new XenForo_Exception("Require Enhanced Search to be installed and enabled", true);
        }
        if (!SV_Utils_AddOn::addOnIsActive('SV_SearchImprovements', 1020000))
        {
            throw new XenForo_Exception("Enhanced Search Improvements support requires v1.2.0 or newer", true);
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
        \SV_Utils_Install::addColumn(
            'xf_thread',
            'word_count',
            "int(10) unsigned
                DEFAULT NULL"
        );
        \SV_Utils_Install::addIndex(
            'xf_thread',
            'word_count',
            array('word_count', 'last_post_date')
        );

        if ($version < 1000703)
        {
            $db->query("truncate table xf_post_words");
            XenForo_Application::defer('SV_WordCountSearch_Deferred_WordCountMigration', array('position' => -1), 'WordCountMigration', true);
        }

        if ($version < 1010001)
        {
            if (SV_Utils_AddOn::addOnIsActive('sidaneThreadmarks', 1030002))
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

        if ($version >= 1010000 && $version < 1010200)
        {
            $requireIndexing['thread'] = true;
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
        \SV_Utils_Install::dropColumn('xf_thread', 'word_count');
    }
}
