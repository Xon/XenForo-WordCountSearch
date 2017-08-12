<?php

class SV_WordCountSearch_XenForo_ControllerPublic_Thread extends XFCP_SV_WordCountSearch_XenForo_ControllerPublic_Thread
{
    protected function _getPostFetchOptions(array $thread, array $forum)
    {
        $postFetchOptions = parent::_getPostFetchOptions($thread, $forum);
        if (!isset($postFetchOptions['skip_wordcount']))
        {
            $postFetchOptions['skip_wordcount'] = true;
        }
        return $postFetchOptions;
    }

    protected function _getThreadmarkFetchOptions(array $thread = null, array $forum = null)
    {
        $fetchOptions = parent::_getThreadmarkFetchOptions($thread);

        if (SV_Utils_AddOn::addOnIsActive('sidaneThreadmarks'))
        {
            $fetchOptions['join'] |= SV_WordCountSearch_Sidane_Threadmarks_Model_Threadmarks::FETCH_WORD_COUNT;
        }

        return $fetchOptions;
    }

    public function actionThreadmarks()
    {
        $response = parent::actionThreadmarks();

        if ($response instanceof XenForo_ControllerResponse_View && isset($response->params['thread']))
        {
            $thread = $response->params['thread'];
            $activeCategory = &$response->params['activeThreadmarkCategory'];
            $categoryId = $activeCategory['threadmark_category_id'];
            if (isset($thread['threadmark_category_data']))
            {
                $data = $thread['threadmark_category_data'];
                if (isset($data[$categoryId]['WordCount']))
                {
                    $activeCategory['WordCount'] = $data[$categoryId]['WordCount'];
                }
            }
            else if (isset($thread['WordCount']))
            {
                $activeCategory['WordCount'] = $thread['WordCount'];
            }
        }

        return $response;
    }
}

// ******************** FOR IDE AUTO COMPLETE ********************
if (false)
{
    class XFCP_SV_WordCountSearch_XenForo_ControllerPublic_Thread extends Sidane_Threadmarks_XenForo_ControllerPublic_Thread {}
}