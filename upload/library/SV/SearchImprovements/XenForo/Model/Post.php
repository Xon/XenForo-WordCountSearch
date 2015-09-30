<?php

class SV_SearchImprovements_XenForo_Model_Post extends XFCP_SV_SearchImprovements_XenForo_Model_Post
{
    function str_word_count_utf8($str)
    {
        // ref: http://php.net/manual/de/function.str-word-count.php#107363
        return count(preg_split('~[^\p{L}\p{N}\']+~u',$str));
    }

    public function getTextWordCount($message)
    {
        return $this->str_word_count_utf8(XenForo_Helper_String::bbCodeStrip($message, true));
    }


    public function preparePostJoinOptions(array $fetchOptions)
    {
        $joinOptions = parent::preparePostJoinOptions($fetchOptions);

        $joinOptions['selectFields'] .= '
            , wordcount.word_count
        ';
        $joinOptions['joinTables'] .= '
            LEFT JOIN xf_post_words wordcount ON wordcount.post_id = post.post_id
        ';

        return $joinOptions;
    }

    protected function roundWordCount($WordCount)
    {
        $ApproximateWordCount = $WordCount;
        if ($ApproximateWordCount > 1000000)
            $ApproximateWordCount = round($ApproximateWordCount / 1000000, 1) . 'm';
        else if ($ApproximateWordCount > 100000)
            $ApproximateWordCount = round($ApproximateWordCount / 100000, 1) * 100 . 'k';
        else if ($ApproximateWordCount > 10000)
            $ApproximateWordCount = round($ApproximateWordCount / 10000, 1) * 10 . 'k';
        else if ($ApproximateWordCount > 1000)
            $ApproximateWordCount = round($ApproximateWordCount / 1000, 1) . 'k';
        else if ($ApproximateWordCount > 100)
            $ApproximateWordCount = round($ApproximateWordCount / 100, 1) * 100;
        else if ($ApproximateWordCount > 10)
            $ApproximateWordCount = round($ApproximateWordCount / 10, 1) * 10;
        else
            $ApproximateWordCount = 10;
        return $ApproximateWordCount;
    }

    public function preparePost(array $post, array $thread, array $forum, array $nodePermissions = null, array $viewingUser = null)
    {
        $post = parent::preparePost($post, $thread, $forum, $nodePermissions, $viewingUser);

        if (isset($post['word_count']))
        {
            $post['WordCount'] = $this->roundWordCount($post['word_count']);
        }

        return $post;
    }
}
