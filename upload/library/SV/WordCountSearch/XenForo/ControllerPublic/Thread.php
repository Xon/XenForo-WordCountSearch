<?php

class SV_WordCountSearch_XenForo_ControllerPublic_Thread extends XFCP_SV_WordCountSearch_XenForo_ControllerPublic_Thread
{
    public function actionThreadmarks()
    {
        if (!is_callable('parent::actionThreadmarks'))
        {
            return $this->getNotFoundResponse();
        }

        $response = parent::actionThreadmarks();

        if (!$response instanceof XenForo_ControllerResponse_View)
        {
            return $response;
        }

        $viewParams = &$response->params;

        $threadId = $this->_input->filterSingle('thread_id', XenForo_Input::UINT);

        $viewParams['totalWordCount'] = $this->_getThreadModel()
            ->getThreadmarkWordCountByThread($threadId);

        return $response;
    }

    protected function _getPostFetchOptions(array $thread, array $forum)
    {
        $postFetchOptions = parent::_getPostFetchOptions($thread, $forum);
        if (!isset($postFetchOptions['skip_wordcount']))
        {
            $postFetchOptions['skip_wordcount'] = true;
        }
        return $postFetchOptions;
    }

    protected function _getThreadmarkFetchOptions()
    {
        $fetchOptions = parent::_getThreadmarkFetchOptions();

        $fetchOptions['join'] |= SV_WordCountSearch_Sidane_Threadmarks_Model_Threadmarks::FETCH_WORD_COUNT;

        return $fetchOptions;
    }
}
