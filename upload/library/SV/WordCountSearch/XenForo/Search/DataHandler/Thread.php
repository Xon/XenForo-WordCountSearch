<?php

class SV_WordCountSearch_XenForo_Search_DataHandler_Thread extends XFCP_SV_WordCountSearch_XenForo_Search_DataHandler_Thread
{
    protected function _insertIntoIndex(XenForo_Search_Indexer $indexer, array $data, array $parentData = null)
    {
        $wordcount = 0;

        if (!empty($data['threadmark_count']))
        {
            $wordcount = $this->_getThreadModel()->getThreadmarkWordCountByThread(
                $data['thread_id'],
                false
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
