<?php

class SV_WordCountSearch_XenForo_Model_Post extends XFCP_SV_WordCountSearch_XenForo_Model_Post
{
    public function preparePostJoinOptions(array $fetchOptions)
    {
        $joinOptions = parent::preparePostJoinOptions($fetchOptions);

        if (empty($fetchOptions['skip_wordcount']))
        {
            $joinOptions['selectFields'] .= '
                , wordcount.word_count
            ';
            $joinOptions['joinTables'] .= '
                LEFT JOIN xf_post_words wordcount ON wordcount.post_id = post.post_id
            ';
        }

        return $joinOptions;
    }

    public function preparePost(array $post, array $thread, array $forum, array $nodePermissions = null, array $viewingUser = null)
    {
        $post = parent::preparePost($post, $thread, $forum, $nodePermissions, $viewingUser);

        $WordCountField = SV_WordCountSearch_Globals::WordCountField;
        if (array_key_exists($WordCountField, $post))
        {
            $searchModel = $this->_getSearchModel();
            if ($post[$WordCountField] === null)
            {
                $post[$WordCountField] = $searchModel->getTextWordCount($post['message']);
            }
            $post['WordCount'] = $searchModel->roundWordCount($post[$WordCountField]);
        }

        return $post;
    }

    public function shouldRecordPostWordCount($postId, $wordCount)
    {
        if ($wordCount >= SV_WordCountSearch_Globals::$wordCountThreshold)
        {
            return true;
        }

        if ($threadmarksModel = $this->_getThreadmarksModel())
        {
            if ($threadmarksModel->getByPostId($postId))
            {
                return true;
            }
        }

        return false;
    }

    protected function _copyPost(array $post, array $targetThread, array $forum)
    {
        $wordcount = null;
        if (array_key_exists('word_count', $post))
        {
            $wordcount = $post['word_count'];
            unset($post['word_count']);
        }

        $newPost = parent::_copyPost($post, $targetThread, $forum);

        $db = XenForo_Application::getDb();
        if ($wordcount !== null)
        {
            if ($wordcount >= SV_WordCountSearch_Globals::$wordCountThreshold)
            {                
                $db->query("
                    insert ignore into xf_post_words (post_id, word_count) values (?,?)
                ", array($newPost['post_id'], $wordcount));
            }
        }
        else if ($wordcount === null)
        {
            $db = XenForo_Application::getDb();
            $db->query("
                insert ignore into xf_post_words (post_id, word_count) select ?, word_count from xf_post_words where post_id = ? and word_count >= ?
            ", array($newPost['post_id'], $post['post_id'], SV_WordCountSearch_Globals::$wordCountThreshold));
        }
    }

    protected function _getSearchModel()
    {
        return $this->getModelFromCache('XenForo_Model_Search');
    }

    protected function _getThreadmarksModel()
    {
        if (!class_exists('Sidane_Threadmarks_Model_Threadmarks'))
        {
            return false;
        }

        return $this->getModelFromCache('Sidane_Threadmarks_Model_Threadmarks');
    }
}
