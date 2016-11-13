<?php

class SV_WordCountSearch_Deferred_ThreadmarkWordCount extends XenForo_Deferred_Abstract
{
    public function execute(array $deferred, array $data, $targetRunTime, &$status)
    {
        $increment = isset($data['batch']) ? $data['batch'] : 1000;
        $min_threadmark_id = isset($data['position']) ? $data['position'] : -1;

        $db = XenForo_Application::getDb();

        $threadmarks = $db->fetchAll($db->limit('
			SELECT threadmarks.post_id, xf_post.message
			FROM threadmarks
            INNER JOIN xf_post ON (xf_post.post_id = threadmarks.post_id)
            LEFT JOIN xf_post_words ON (xf_post_words.post_id = threadmarks.post_id)
			WHERE threadmarks.threadmark_id > ? AND xf_post_words.post_id IS NULL
			ORDER BY threadmarks.post_id
		', $increment), $min_threadmark_id);

        if (empty($threadmarks))
        {
            return false;
        }

        $searchModel = XenForo_Model::create('XenForo_Model_Search');
        $min_threadmark_id = false;

        foreach ($threadmarks as $threadmark)
        {
            $min_threadmark_id = $threadmark['threadmark_id'];
            $wordCount = $searchModel->getTextWordCount($threadmark['message']);

            $db->query(
                "INSERT IGNORE xf_post_words (post_id, word_count) VALUES(?,?)",
                array($threadmark['post_id'], $wordCount)
            );
        }

        $actionPhrase = new XenForo_Phrase('rebuilding');
        $typePhrase = new XenForo_Phrase('post');
        $wordCount = new XenForo_Phrase('word_count');
        $status = sprintf(
            '%s... %s %s (%s)',
            $actionPhrase,
            $typePhrase,
            $wordCount,
            XenForo_Locale::numberFormat($data['position'])
        );

        if (empty($min_threadmark_id))
        {
            return false;
        }

        return array('position' => $min_threadmark_id);
    }

    public function canCancel()
    {
        return false;
    }
}
