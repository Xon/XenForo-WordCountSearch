<?php

class SV_WordCountSearch_XenForo_DataWriter_Discussion_Thread extends XFCP_SV_WordCountSearch_XenForo_DataWriter_Discussion_Thread
{
    protected function _getFields()
    {
        $fields = parent::_getFields();

        $fields['xf_thread']['word_count'] = array(
            'type'         => self::TYPE_UNKNOWN,
            'verification' => array('$this', '_verifyWordCount')
        );

        return $fields;
    }

    public function rebuildDiscussionCounters($replyCount = false, $firstPostId = false, $lastPostId = false)
    {
        parent::rebuildDiscussionCounters($replyCount, $firstPostId, $lastPostId);

        if (SV_Utils_AddOn::addOnIsActive('sidaneThreadmarks', 1030002))
        {
            /** @var SV_WordCountSearch_XenForo_Model_Thread $threadModel */
            $threadModel = $this->_getThreadModel();
            $wordCount = $threadModel->countThreadmarkWordsInThread($this->get('thread_id'));
            $this->set('word_count', $wordCount);
        }
    }

    protected function _verifyWordCount($wordCount)
    {
        if (is_int($wordCount) || is_null($wordCount)) {
            return true;
        }

        return false;
    }
}

// ******************** FOR IDE AUTO COMPLETE ********************
if (false)
{
    class XFCP_SV_WordCountSearch_XenForo_DataWriter_Discussion_Thread extends XenForo_DataWriter_Discussion_Thread {}
}