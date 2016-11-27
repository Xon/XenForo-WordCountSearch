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
                $post = $this->_getPostModel()->getPostById($this->get('post_id'));

                if (!$post['word_count'])
                {
                    $wordCount = $this->_getSearchModel()->getTextWordCount($post['message']);

                    $this->_db->query(
                        "INSERT IGNORE xf_post_words (post_id, word_count)
                            VALUES (?,?)",
                        array($this->get('post_id'), $wordCount)
                    );
                }

                $this->_getThreadModel()->rebuildThreadWordCount($this->get('thread_id'));
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
                    $this->_getThreadModel()->rebuildThreadWordCount($this->get('thread_id'));
                    $this->_updateThreadSearchIndex();
                }
            }
        }
    }

    protected function _postDelete()
    {
        parent::_postDelete();

        $post = $this->_getPostModel()->getPostById($this->get('post_id'));

        if ($post['word_count'] < SV_WordCountSearch_Globals::$wordCountThreshold)
        {
            $this->_db->delete(
                'xf_post_words',
                'post_id = ' . $this->_db->quote($this->get('post_id'))
            );
        }

        $this->_getThreadModel()->rebuildThreadWordCount($this->get('thread_id'));
        $this->_updateThreadSearchIndex();
    }

    protected function _getThreadData()
    {
        if (is_callable('parent::_getThreadData'))
        {
            return parent::_getThreadData();
        }
        if (!$thread = $this->getExtraData(self::DATA_THREAD))
        {
            $thread = $this->_getThreadModel()->getThreadById($this->get('thread_id'));
            $this->setExtraData(self::DATA_THREAD, $thread);
        }

        return $thread;
    }

    protected function _updateThreadSearchIndex()
    {
        $indexer = new XenForo_Search_Indexer();

        $thread = $this->_getThreadData();

        $threadHandler = XenForo_Search_DataHandler_Abstract::create(
            'XenForo_Search_DataHandler_Thread'
        );

        $threadHandler->insertIntoIndex($indexer, $thread);
    }

    protected function _getPostModel()
    {
        return $this->getModelFromCache('XenForo_Model_Post');
    }

    protected function _getSearchModel()
    {
        return $this->getModelFromCache('XenForo_Model_Search');
    }
}
