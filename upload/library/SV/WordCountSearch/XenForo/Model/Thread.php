<?php

class SV_WordCountSearch_XenForo_Model_Thread extends XFCP_SV_WordCountSearch_XenForo_Model_Thread
{
    public function countThreadmarkWordsInThread($threadId)
    {
        if (!SV_Utils_AddOn::addOnIsActive('sidaneThreadmarks'))
        {
            return 0;
        }

        $args = array($threadId);
        $sql = '';
        if (SV_Utils_AddOn::addOnIsActive('sidaneThreadmarks', 1050015))
        {
            $sql = ' AND threadmarks.threadmark_category_id = ? ';
            $args[] = 1; // only count the 1st threadmark type, hardcode for now
        }

        $wordCount = $this->_getDb()->fetchOne("
            SELECT sum(post_words.word_count)
            FROM threadmarks
            INNER JOIN xf_post_words AS post_words ON
                (post_words.post_id = threadmarks.post_id)
            WHERE threadmarks.thread_id = ? {$sql}
                AND threadmarks.message_state = 'visible'
        ", $args);

        return intval($wordCount);
    }

    public function rebuildThreadWordCount($threadId)
    {
        $wordCount = $this->countThreadmarkWordsInThread($threadId);

        $dw = XenForo_DataWriter::create('XenForo_DataWriter_Discussion_Thread');
        $dw->setExistingData($threadId);
        $dw->set('word_count', $wordCount);
        $dw->save();

        return $wordCount;
    }

    public function prepareThread(array $thread, array $forum, array $nodePermissions = null, array $viewingUser = null)
    {
        $thread = parent::prepareThread($thread, $forum, $nodePermissions, $viewingUser);
        $searchModel = $this->_getSearchModel();
        if (isset($thread['word_count']))
        {
            $thread['WordCount'] = $searchModel->roundWordCount($thread['word_count']);
        }
        if (isset($thread['threadmark_category_data']))
        {
            foreach($thread['threadmark_category_data'] as &$category)
            {
                if (isset($category['word_count']))
                {
                    $category['WordCount'] = $searchModel->roundWordCount($category['word_count']);
                }
            }
        }
        return $thread;
    }

    public function prepareThreadFetchOptions(array $fetchOptions)
    {
        $joinOptions = parent::prepareThreadFetchOptions($fetchOptions);

        if (!empty($fetchOptions['order']) && $fetchOptions['order'] === 'word_count')
        {
            $orderBy = 'thread.word_count';
            $orderBySecondary = ', thread.last_post_date DESC';
            if (!isset($fetchOptions['orderDirection']) || $fetchOptions['orderDirection'] == 'desc')
            {
                $orderBy .= ' DESC';
            }
            else
            {
                $orderBy .= ' ASC';
            }
            $orderBy .= $orderBySecondary;
            $joinOptions['orderClause'] = ($orderBy ? "ORDER BY $orderBy" : '');
        }

        return $joinOptions;
    }

    public function prepareThreadConditions(array $conditions, array &$fetchOptions)
    {
        $sql = parent::prepareThreadConditions($conditions, $fetchOptions);

        $sqlConditions = array($sql);

        if (isset($conditions['lword']) && ($value = intval($conditions['lword'])))
        {
            $sqlConditions[] = ' thread.word_count >= ' . $value;
        }

        if (isset($conditions['uword']) && ($value = intval($conditions['uword'])))
        {
            $sqlConditions[] = ' thread.word_count <= ' . $value;
        }

        if (count($sqlConditions) == 1)
        {
            return $sql;
        }

        return $this->getConditionsForClause($sqlConditions);
    }

	/**
	 * @return SV_WordCountSearch_XenForo_Model_Search
	 */
    protected function _getSearchModel()
    {
        return $this->getModelFromCache('XenForo_Model_Search');
    }
}

// ******************** FOR IDE AUTO COMPLETE ********************
if (false)
{
    class XFCP_SV_WordCountSearch_XenForo_Model_Thread extends XenForo_Model_Thread {}
}
