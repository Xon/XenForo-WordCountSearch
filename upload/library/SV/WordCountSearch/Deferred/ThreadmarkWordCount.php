<?php

class SV_WordCountSearch_Deferred_ThreadmarkWordCount extends XenForo_Deferred_Abstract
{
    public function execute(array $deferred, array $data, $targetRunTime, &$status)
    {
        if (!SV_Utils_AddOn::addOnIsActive('sidaneThreadmarks') || !SV_Utils_AddOn::addOnIsActive('SV_WordCountSearch'))
        {
            return false;
        }
        $increment = isset($data['batch']) ? $data['batch'] : 1000;
        $min_threadmark_id = isset($data['position']) ? $data['position'] : -1;

        $db = XenForo_Application::getDb();

		if (!$db->fetchRow("SHOW TABLES LIKE 'xf_post_words'"))
		{
			return false;
		}

        $args = array($min_threadmark_id);
        $sql = '';
        if (SV_Utils_AddOn::addOnIsActive('sidaneThreadmarks', 1050015))
        {
            $sql = ' AND threadmarks.threadmark_category_id = ? ';
            $args[] = 1; // only count the 1st threadmark type, hardcode for now
        }

        $threadmarks = $db->fetchAll($db->limit('
            SELECT threadmarks.threadmark_id, threadmarks.post_id, xf_post.message, xf_post.thread_id
            FROM threadmarks
            INNER JOIN xf_post ON (xf_post.post_id = threadmarks.post_id)
            LEFT JOIN xf_post_words ON (xf_post_words.post_id = threadmarks.post_id)
            WHERE threadmarks.threadmark_id > ? AND xf_post_words.post_id IS NULL ' . $sql . '
            ORDER BY threadmarks.threadmark_id
        ', $increment), $args);

        if (empty($threadmarks))
        {
            return false;
        }

        /** @var SV_WordCountSearch_XenForo_Model_Search $searchModel */
        $searchModel = XenForo_Model::create('XenForo_Model_Search');
        /** @var SV_WordCountSearch_XenForo_Model_Thread $threadModel */
        $threadModel = XenForo_Model::create('XenForo_Model_Thread');
        /** @var SV_WordCountSearch_Sidane_Threadmarks_Model_Threadmarks $threadmarkModel */
        $threadmarkModel = SV_Utils_AddOn::addOnIsActive('sidaneThreadmarks', 1030002) ? XenForo_Model::create('Sidane_Threadmarks_Model_Threadmarks') : null;
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

        $threadIds = array_unique(XenForo_Application::arrayColumn($threadmarks, 'thread_id'));
        foreach($threadIds as $threadId)
        {
            $threadModel->rebuildThreadWordCount($threadId);
            if ($threadmarkModel)
            {
                $threadmarkModel->updateThreadmarkDataForThread($threadId);
            }
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
