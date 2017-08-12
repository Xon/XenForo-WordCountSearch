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
                $joinTables   .= 'LEFT JOIN xf_post_words AS post_words ON
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

  protected function getPerTheadmarkCategoryData($threadId)
  {
    return $this->fetchAllKeyed(
      'SELECT threadmark_category_id, MAX(position) AS position, sum(COALESCE(post_words.word_count, 0)) as word_count
        FROM threadmarks
        LEFT JOIN xf_post_words AS post_words ON (post_words.post_id = threadmarks.post_id)
        WHERE thread_id = ?
        GROUP BY threadmark_category_id',
      'threadmark_category_id',
      $threadId
    );
  }

    protected function _getSearchModel()
    {
        return $this->getModelFromCache('XenForo_Model_Search');
    }
}

// ******************** FOR IDE AUTO COMPLETE ********************
if (false)
{
    class XFCP_SV_WordCountSearch_Sidane_Threadmarks_Model_Threadmarks extends Sidane_Threadmarks_Model_Threadmarks {}
}