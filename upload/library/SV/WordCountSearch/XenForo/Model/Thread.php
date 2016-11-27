<?php

class SV_WordCountSearch_XenForo_Model_Thread extends XFCP_SV_WordCountSearch_XenForo_Model_Thread
{
    public function countThreadmarkWordsInThread($threadId)
    {
        $posts = $this->_getDb()->fetchAll("
            SELECT post_words.word_count
            FROM threadmarks
            INNER JOIN xf_post_words AS post_words ON
                (post_words.post_id = threadmarks.post_id)
            WHERE threadmarks.thread_id = ?
                AND threadmarks.message_state = 'visible'
        ", $threadId);

        $wordCount = 0;
        foreach ($posts as $post)
        {
            $wordCount += $post['word_count'];
        }

        return $wordCount;
    }

    public function rebuildThreadWordCount($threadId)
    {
        $wordCount = $this->countThreadmarkWordsInThread($threadId);

        $dw = XenForo_DataWriter::create('XenForo_DataWriter_Discussion_Thread');
        $dw->setExistingData($threadId);
        $dw->set('word_count', $wordCount);
        $dw->save();

        return $wordCount;
    }

    public function prepareThread(array $thread, array $forum, array $nodePermissions = null, array $viewingUser = null)
    {
        if (isset($thread['word_count']))
        {
            $thread['WordCount'] = $this->_getSearchModel()->roundWordCount($thread['word_count']);
        }
        return parent::prepareThread($thread, $forum, $nodePermissions, $viewingUser);
    }

    protected function _getSearchModel()
    {
        return $this->getModelFromCache('XenForo_Model_Search');
    }
}
