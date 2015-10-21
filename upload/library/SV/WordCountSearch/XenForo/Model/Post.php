<?php

class SV_WordCountSearch_XenForo_Model_Post extends XFCP_SV_WordCountSearch_XenForo_Model_Post
{
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

    public function preparePost(array $post, array $thread, array $forum, array $nodePermissions = null, array $viewingUser = null)
    {
        $post = parent::preparePost($post, $thread, $forum, $nodePermissions, $viewingUser);

        if (isset($post['word_count']))
        {
            $post['WordCount'] = $this->_getSearchModel()->roundWordCount($post['word_count']);
        }

        return $post;
    }

    protected function _getSearchModel()
    {
        return $this->getModelFromCache('XenForo_Model_Search');
    }
}