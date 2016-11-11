<?php

class SV_WordCountSearch_Sidane_Threadmarks_DataWriter_Threadmark extends XFCP_SV_WordCountSearch_Sidane_Threadmarks_DataWriter_Threadmark
{
    protected function _postSave()
    {
        if ($this->isInsert())
        {
            $this->_invalidateThreadWordCountCacheEntry();
            $this->_updateSearchIndexes();
        }
        elseif ($this->isUpdate())
        {
            if ($this->isChanged('message_state'))
            {
                if (
                    $this->get('message_state') === 'visible' ||
                    $this->getExisting('message_state') === 'visible'
                )
                {
                    $this->_invalidateThreadWordCountCacheEntry();
                    $this->_updateSearchIndexes();
                }
            }
        }
    }

    protected function _postDelete()
    {
        parent::_postDelete();

        $this->_db->delete(
            'xf_post_words',
            'post_id = ' . $this->_db->quote($this->get('post_id'))
        );

        $this->_invalidateThreadWordCountCacheEntry();
        $this->_updateSearchIndexes();
    }

    protected function _invalidateThreadWordCountCacheEntry()
    {
        $cache = \XenForo_Application::getCache();

        if ($cache)
        {
            $cacheKey  = 'SV_WordCountSearch_threadmarks';
            $cacheKey .= "_thread{$this->get('thread_id')}";

            $cache->remove($cacheKey);
        }
    }

    protected function _updateSearchIndexes()
    {
        $indexer = new XenForo_Search_Indexer();
        $thread = $this->_getThreadModel()->getThreadById(
            $this->get('thread_id')
        );

        $threadHandler = XenForo_Search_DataHandler_Abstract::create(
            'XenForo_Search_DataHandler_Thread'
        );

        $threadHandler->insertIntoIndex($indexer, $thread);

        $post = $this->_getPostModel()->getPostById($this->get('post_id'));

        $postHandler = XenForo_Search_DataHandler_Abstract::create(
            'XenForo_Search_DataHandler_Post'
        );

        $postHandler->insertIntoIndex($indexer, $post, $thread);
    }

    protected function _getPostModel()
    {
        return $this->getModelFromCache('XenForo_Model_Post');
    }
}
