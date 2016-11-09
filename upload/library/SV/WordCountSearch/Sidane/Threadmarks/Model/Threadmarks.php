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
}
