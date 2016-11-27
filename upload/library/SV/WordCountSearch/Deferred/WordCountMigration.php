<?php

class SV_WordCountSearch_Deferred_WordCountMigration extends XenForo_Deferred_Abstract
{
    public function execute(array $deferred, array $data, $targetRunTime, &$status)
    {
        $increment = 1000;
        $min_post_id = isset($data['position']) ? $data['position'] : -1;

        $db = XenForo_Application::getDb();

        $posts = $db->fetchAll($db->limit('
			SELECT xf_post.post_id, xf_post.message
			FROM xf_post
            left join xf_post_words on xf_post_words.post_id = xf_post.post_id
			WHERE xf_post.post_id > ? and xf_post_words.post_id is null
			ORDER BY xf_post.post_id
		', $increment), $min_post_id);

        if (empty($posts))
        {
           return false;
        }


        $searchModel = XenForo_Model::create('XenForo_Model_Search');
        $wordCountThreshold = $searchModel->getWordCountThreshold();
        $min_post_id = false;
        foreach($posts as $post)
        {
            $min_post_id = $post['post_id'];
            $wordCount = $searchModel->getTextWordCount($post['message']);
            if ($wordCount >= $wordCountThreshold)
            {
                $db->query("
                    insert ignore xf_post_words (post_id, word_count) values(?,?)
                ", array($post['post_id'], $wordCount));
            }
        }
		$actionPhrase = new XenForo_Phrase('rebuilding');
        $typePhrase = new XenForo_Phrase('post');
		$wordCount = new XenForo_Phrase('word_count');
        $status = sprintf('%s... %s %s (%s)', $actionPhrase, $typePhrase, $wordCount, XenForo_Locale::numberFormat($data['position']));

        if (empty($min_post_id))
        {
            return false;
        }

        return array('position' => $min_post_id);
    }

    public function canCancel()
    {
        return false;
    }
}
