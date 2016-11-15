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

        $wordCount = $this->_getThreadModel()->countThreadmarkWordsInThread($this->get('thread_id'));

        $this->set('word_count', $wordCount);
    }

    protected function _verifyWordCount($wordCount)
    {
        if (!is_int($wordCount) && !is_null($wordCount)) {
            return false;
        }

        return true;
    }
}
