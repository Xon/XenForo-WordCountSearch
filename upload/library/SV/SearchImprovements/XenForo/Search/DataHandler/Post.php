<?php

class SV_SearchImprovements_XenForo_Search_DataHandler_Post extends XFCP_SV_SearchImprovements_XenForo_Search_DataHandler_Post
{
    protected function _insertIntoIndex(XenForo_Search_Indexer $indexer, array $data, array $parentData = null)
    {
        if (isset($data[SV_SearchImprovements_Globals::WordCountField]))
        {
            $metadata = array();
            $metadata[SV_SearchImprovements_Globals::WordCountField] = $data[SV_SearchImprovements_Globals::WordCountField];

            if ($indexer instanceof SV_SearchImprovements_Search_IndexerProxy)
            {
                $indexer->setProxyMetaData($metadata);
            }
            else
            {
                $indexer = new SV_SearchImprovements_Search_IndexerProxy($indexer, $metadata);
            }
        }

        parent::_insertIntoIndex($indexer, $data, $parentData);
    }

    public function quickIndex(XenForo_Search_Indexer $indexer, array $contentIds)
    {
        $indexer = new SV_SearchImprovements_Search_IndexerProxy($indexer, array());
        return parent::quickIndex($indexer, $contentIds);
    }
}