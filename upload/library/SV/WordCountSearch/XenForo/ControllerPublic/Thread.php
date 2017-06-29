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
}

// ******************** FOR IDE AUTO COMPLETE ********************
if (false)
{
    class XFCP_SV_WordCountSearch_XenForo_ControllerPublic_Thread extends Sidane_Threadmarks_XenForo_ControllerPublic_Thread {}
}