<?php

class SV_WordCountSearch_Sidane_Threadmarks_DataWriter_Threadmark extends XFCP_SV_WordCountSearch_Sidane_Threadmarks_DataWriter_Threadmark
{
    protected function _postSaveAfterTransaction()
    {
        parent::_postSaveAfterTransaction();

        if ($this->isInsert())
        {
            if ($this->get('message_state') == 'visible')
            {
                $this->_invalidateThreadWordCountCacheEntry();
                $this->_updateThreadSearchIndex();
            }
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
                    $this->_updateThreadSearchIndex();
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
        $this->_updateThreadSearchIndex();
    }

    protected function _invalidateThreadWordCountCacheEntry()
    {
        $cache = XenForo_Application::getCache();

        if ($cache)
        {
            $cache->remove("SV_WordCountSearch_threadmarks_thread{$this->get('thread_id')}");
        }
    }

    protected function _updateThreadSearchIndex()
    {
        $indexer = new XenForo_Search_Indexer();
        $thread = $this->_getThreadModel()->getThreadById(
            $this->get('thread_id')
        );

        $threadHandler = XenForo_Search_DataHandler_Abstract::create(
            'XenForo_Search_DataHandler_Thread'
        );

        $threadHandler->insertIntoIndex($indexer, $thread);
    }

    protected function _getPostModel()
    {
        return $this->getModelFromCache('XenForo_Model_Post');
    }
}
