<?php

class SV_WordCountSearch_Sidane_Threadmarks_Model_Threadmarks extends XFCP_SV_WordCountSearch_Sidane_Threadmarks_Model_Threadmarks
{
    const FETCH_WORD_COUNT = 0x20;

    public function prepareThreadmarkJoinOptions(array $fetchOptions)
    {
        $joinOptions = parent::prepareThreadmarkJoinOptions($fetchOptions);

        $selectFields = $joinOptions['selectFields'];
        $joinTables   = $joinOptions['joinTables'];

        if (!empty($fetchOptions['join']))
        {
            if ($fetchOptions['join'] & self::FETCH_WORD_COUNT)
            {
                $selectFields .= ', post_words.word_count';
                $joinTables   .= 'JOIN xf_post_words AS post_words ON
                    (post_words.post_id = threadmarks.post_id)';
            }
        }

        return array(
            'selectFields' => $selectFields,
            'joinTables'   => $joinTables
        );
    }

    public function prepareThreadmark(array $threadmark, array $thread, array $forum, array $nodePermissions = null, array $viewingUser = null)
    {
        if (!empty($threadmark['word_count']))
        {
            $threadmark['WordCount'] = $this->_getSearchModel()->roundWordCount($threadmark['word_count']);
        }
        return parent::prepareThreadmark($threadmark, $thread, $forum, $nodePermissions, $viewingUser);
    }

    protected function _getSearchModel()
    {
        return $this->getModelFromCache('XenForo_Model_Search');
    }
}
