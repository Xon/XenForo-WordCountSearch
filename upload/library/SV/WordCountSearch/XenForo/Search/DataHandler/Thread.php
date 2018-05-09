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
        $searchModel = $this->_getSearchModel();
        $wordCount = empty($data['word_count']) ? 0 : intval($data['word_count']);

        if (!empty($data['threadmark_count']) && !$wordCount ||
            empty($data['threadmark_count']) && $wordCount)
        {
            /** @var SV_WordCountSearch_XenForo_Model_Thread $threadModel */
            $threadModel = $this->_getThreadModel();
            $wordCount = $threadModel->rebuildThreadWordCount(
                $data['thread_id']
            );
        }

        $metadata = array();
        if ($searchModel->pushWordCountInIndex() && $wordCount > 0)
        {
            $metadata['word_count'] = $wordCount;
        }

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

    /**
     * @var SV_WordCountSearch_XenForo_Model_Search
     */
    protected $_searchModel = null;

    /**
     * @return SV_WordCountSearch_XenForo_Model_Search
     * @throws XenForo_Exception
     */
    protected function _getSearchModel()
    {
        if (!$this->_searchModel)
        {
            $this->_searchModel = XenForo_Model::create('XenForo_Model_Search');
        }

        return $this->_searchModel;
    }
}

// ******************** FOR IDE AUTO COMPLETE ********************
if (false)
{
    class XFCP_SV_WordCountSearch_XenForo_Search_DataHandler_Thread extends XenForo_Search_DataHandler_Thread {}
}