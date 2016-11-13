<?php

class SV_WordCountSearch_XenForo_Model_Thread extends XFCP_SV_WordCountSearch_XenForo_Model_Thread
{
    /**
     * The TTL for cached thread word count queries. Default is 4 hours.
     */
    const WORD_COUNT_CACHE_TTL = 14400;

    public function getThreadmarkWordCountByThread($threadId, $cache = true)
    {
        if ($cache)
        {
            if ($cache = XenForo_Application::getCache())
            {
                $cacheKey = "SV_WordCountSearch_threadmarks_thread{$threadId}";

                $wordCount = $cache->load($cacheKey);

                if ($wordCount)
                {
                    return $wordCount;
                }
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
            $cache->save(
                (string) $wordCount,
                $cacheKey,
                array(),
                self::WORD_COUNT_CACHE_TTL
            );
        }

        return $wordCount;
    }
}
