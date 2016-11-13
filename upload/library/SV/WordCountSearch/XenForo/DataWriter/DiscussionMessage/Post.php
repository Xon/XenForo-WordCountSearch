<?php

class SV_WordCountSearch_XenForo_DataWriter_DiscussionMessage_Post extends XFCP_SV_WordCountSearch_XenForo_DataWriter_DiscussionMessage_Post
{
    protected $_includeWordCount = true;
    protected function _getFields()
    {
        $fields = parent::_getFields();
        if ($this->_includeWordCount)
        {
            $fields['xf_post_words'] = array
            (
                'post_id' => array('type' => self::TYPE_UINT,   'default' => array('xf_post', 'post_id'), 'required' => true),
                'word_count' => array('type' => self::TYPE_UINT, 'default' => 0)
            );
        }
        return $fields;
    }

    protected function _getExistingData($data)
    {
        $postData = parent::_getExistingData($data);
        if (isset($postData['xf_post']['word_count']))
        {
            if ($this->_includeWordCount)
            {
                $postData['xf_post_words'] = array('post_id' => $postData['xf_post']['post_id'], 'word_count' => $postData['xf_post']['word_count']);
            }
            unset($postData['xf_post']['word_count']);
        }
        return $postData;
    }

    protected function _messagePreDelete()
    {
        // prevent the datawriter trying to delete the xf_post_words row and erroring with "Cannot delete data without a condition"
        $this->_includeWordCount = false;
        unset($this->_fields['xf_post_words']);
        parent::_messagePreDelete();
    }

    protected function _messagePostDelete()
    {
        parent::_messagePostDelete();
        $db = $this->_db;
        $db->query('delete from xf_post_words where post_id = ?', array($this->get('post_id')));
    }

    protected $_wordCount = null;
    protected $deferredWordCountInsert = false;

    protected function _messagePreSave()
    {
        parent::_messagePreSave();
        if ($this->isChanged('message') || $this->isInsert())
        {
            $this->_wordCount = $this->_getSearchModel()->getTextWordCount($this->get('message'));
            $this->set('word_count', $this->_wordCount);
        }
        if ($this->_wordCount)
        {
            $db = $this->_db;
            if (!$this->_getPostModel()->shouldRecordPostWordCount($this->get('post_id'), $this->_wordCount))
            {
                if ($this->getExisting('word_count'))
                {
                    $post_id = $this->get('post_id');
                    $db->query('delete from xf_post_words where post_id = ?', array($this->get('post_id')));
                }
            }
            else
            {
                $this->deferredWordCountInsert = true;
            }
            $this->_includeWordCount = false;
            unset($this->_fields['xf_post_words']);
            unset($this->_newData['xf_post_words']);
            unset($this->_existingData['xf_post_words']);
        }
    }

    protected function _messagePostSave()
    {
        $this->_includeWordCount = true;
        if ($this->_wordCount !== null)
        {
            $this->_fields = $this->_getFields();
            $this->_newData['xf_post_words']['word_count'] = $this->_wordCount;
        }
        if ($this->deferredWordCountInsert)
        {
            $db = $this->_db;
            $db->query("
                insert xf_post_words (post_id, word_count)
                values (?,?)
                on duplicate key update
                    word_count = values(word_count)
            ", array($this->get('post_id'), $this->_wordCount));

            if ($threadmarksModel = $this->_getThreadmarksModel())
            {
                if ($threadmarksModel->getByPostId($this->get('post_id')))
                {
                    $this->_invalidateThreadWordCountCacheEntry();
                    $this->_updateThreadSearchIndex();
                }
            }
        }

        parent::_messagePostSave();
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
        $thread = $this->getDiscussionData();

        $threadHandler = XenForo_Search_DataHandler_Abstract::create(
            'XenForo_Search_DataHandler_Thread'
        );

        $threadHandler->insertIntoIndex($indexer, $thread);
    }

    protected function _getSearchModel()
    {
        return $this->getModelFromCache('XenForo_Model_Search');
    }

    protected function _getThreadmarksModel()
    {
        if (!class_exists('Sidane_Threadmarks_Model_Threadmarks'))
        {
            return false;
        }

        return $this->getModelFromCache('Sidane_Threadmarks_Model_Threadmarks');
    }
}
