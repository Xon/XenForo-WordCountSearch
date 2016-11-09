<?php

class SV_WordCountSearch_XenForo_Model_Thread extends XFCP_SV_WordCountSearch_XenForo_Model_Thread
{
    public function getThreadmarkWordCountByThread($threadId)
    {
        $cache = \XenForo_Application::getCache();

        if ($cache)
        {
            $cacheKey = 'SV_WordCountSearch_threadmarks';
            $cacheKey .= "_thread{$threadId}";

            $wordCount = unserialize($cache->load($cacheKey));

            if ($wordCount)
            {
                return $wordCount;
            }
        }

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

        if ($cache)
        {
            $cache->save(serialize($wordCount), $cacheKey, array(), 14400);
        }

        return $wordCount;
    }
}
