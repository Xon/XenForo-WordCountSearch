<?php

class SV_WordCountSearch_XenForo_Search_DataHandler_Thread extends XFCP_SV_WordCountSearch_XenForo_Search_DataHandler_Thread
{
    public function getCustomMapping(array $mapping = array())
    {
        if (is_callable('parent::getCustomMapping'))
        {
            $mapping = parent::getCustomMapping($mapping);
        }
        $mapping['properties']['word_count'] = array("type" => "long");
        return $mapping;
    }

    protected function _insertIntoIndex(XenForo_Search_Indexer $indexer, array $data, array $parentData = null)
    {
        $wordcount = 0;

        if (!empty($data['threadmark_count']) && empty($data['word_count']) ||
            empty($data['threadmark_count']) && !empty($data['word_count']))
        {
            $wordcount = $this->_getThreadModel()->rebuildThreadWordCount(
                $data['thread_id']
            );
        }

        $metadata = array();
        $metadata[SV_WordCountSearch_Globals::WordCountField] = $wordcount;

        if ($indexer instanceof SV_SearchImprovements_Search_IndexerProxy)
        {
            $indexer->setProxyMetaData($metadata);
        }
        else
        {
            $indexer = new SV_SearchImprovements_Search_IndexerProxy(
                $indexer,
                $metadata
            );
        }

        parent::_insertIntoIndex($indexer, $data, $parentData);
    }

    public function quickIndex(XenForo_Search_Indexer $indexer, array $contentIds)
    {
        $indexer = new SV_SearchImprovements_Search_IndexerProxy($indexer, array());

        return parent::quickIndex($indexer, $contentIds);
    }
}
