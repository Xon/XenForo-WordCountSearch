<?php

class SV_SearchImprovements_XenES_Model_Elasticsearch extends XFCP_SV_SearchImprovements_XenES_Model_Elasticsearch
{
    private static $sv_addedWordCount = false;

    private function sv_addWordCountToGenericMappings()
    {
        if (self::$sv_addedWordCount)
        {
            return;
        }
        self::$sv_addedWordCount = true;
        
        self::$optimizedGenericMapping["properties"]["message"]["fields"] = array(
            "word_count" => array(
              "type" => "token_count",
              "store" => "yes",
              "analyzer" => "standard"
        ));
/*
        $stemmingConfig = $this->getStemmingConfiguration();
        switch ($stemmingConfig['analyzer'])
        {
            case 'snowball': $analyzer = array('type' => 'snowball', 'language' => $stemmingConfig['language']); break;
            case 'standard': $analyzer = array('type' => 'standard'); break;
            default: $analyzer = false;
        }

        if ($analyzer)
        {
            self::$optimizedGenericMapping["properties"]["message"]["fields"]["word_count"]["analyzer"] = $analyzer;
        }
*/
    }

    public function getOptimizableMappings(array $mappingTypes = null, $mappings = null)
    {
        $this->sv_addWordCountToGenericMappings();
        return parent::getOptimizableMappings($mappingTypes, $mappings);
    }

    public function optimizeMapping($type, $deleteFirst = true, array $extra = array())
    {
        $this->sv_addWordCountToGenericMappings();
        return parent::optimizeMapping($type, $deleteFirst, $extra);
    }
}