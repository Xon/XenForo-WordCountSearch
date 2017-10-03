<?php

class SV_WordCountSearch_XenForo_Search_DataHandler_Post extends XFCP_SV_WordCountSearch_XenForo_Search_DataHandler_Post
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
        if (!isset($data['word_count']))
        {
            $wordCount = $searchModel->getTextWordCount($data['message']);
            if ($wordCount >= $searchModel->getWordCountThreshold())
            {
                $db = XenForo_Application::getDb();
                $db->query("
                    insert ignore into xf_post_words (post_id, word_count) values (?,?)
                ", array($data['post_id'], $wordCount));
            }
            $data['word_count'] = $wordCount;
        }

        $metadata = array();
        if ($searchModel->pushWordCountInIndex() && $data['word_count'] > 0)
        {
            $metadata['word_count'] = $data['word_count'];
        }

        if ($indexer instanceof SV_SearchImprovements_Search_IndexerProxy)
        {
            $indexer->setProxyMetaData($metadata);
        }
        else
        {
            $indexer = new SV_SearchImprovements_Search_IndexerProxy($indexer, $metadata);
        }

        parent::_insertIntoIndex($indexer, $data, $parentData);
    }

    public function getSearchFormControllerResponse(XenForo_ControllerPublic_Abstract $controller, XenForo_Input $input, array $viewParams)
    {
        if (!isset($viewParams['sortOptions']))
        {
            $viewParams['sortOptions'] = array();
        }
        $viewParams['sortOptions'][] = array('id' => 'word_count', 'phrase' => new XenForo_Phrase('word_count'));
        return parent::getSearchFormControllerResponse($controller, $input, $viewParams);
    }

    /**
     * @var SV_WordCountSearch_XenForo_Model_Search
     */
    protected $_searchModel = null;
    /**
     * @return SV_WordCountSearch_XenForo_Model_Search
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
    class XFCP_SV_WordCountSearch_XenForo_Search_DataHandler_Post extends XenForo_Search_DataHandler_Post {}
}