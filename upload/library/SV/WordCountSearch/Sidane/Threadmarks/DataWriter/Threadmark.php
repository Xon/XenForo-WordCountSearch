<?php

class SV_WordCountSearch_Sidane_Threadmarks_DataWriter_Threadmark extends XFCP_SV_WordCountSearch_Sidane_Threadmarks_DataWriter_Threadmark
{
    protected function _postSave()
    {
        if ($this->isInsert())
        {
            $this->_invalidateThreadWordCountCacheEntry();
        }
        elseif ($this->isUpdate())
        {
            if ($this->isChanged('message_state')
            {
                if (
                    $this->get('message_state') === 'visible' or
                    $this->getExisting('message_state') === 'visible'
                )
                {
                    $this->_invalidateThreadWordCountCacheEntry();
                }
            }
        }
    }

    protected function _postDelete()
    {
        parent::_postDelete();

        $this->_invalidateThreadWordCountCacheEntry();
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
}
