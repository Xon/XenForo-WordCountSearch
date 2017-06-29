<?php

class SV_WordCountSearch_XenForo_ControllerPublic_Search extends XFCP_SV_WordCountSearch_XenForo_ControllerPublic_Search
{
    public function actionIndex()
    {
        SV_WordCountSearch_Globals::$SearchController = $this;
        $response = parent::actionIndex();

        if ($response instanceof XenForo_ControllerResponse_View)
        {
            if (!empty($response->params['search']))
            {
                $params = $this->_input->filter(array(
                    'c' => XenForo_Input::ARRAY_SIMPLE
                ));

                if (isset($params['c']['word_count'][0]))
                {
                    $response->params['search']['word_count']['lower'] = $params['c']['word_count'][0];
                }
                if (isset($params['c']['word_count'][1]))
                {
                    $response->params['search']['word_count']['upper'] = $params['c']['word_count'][1];
                }
            }
            /** @var SV_WordCountSearch_XenForo_Model_Search $searchModel */
            $searchModel = $this->_getSearchModel();
            $response->params['search']['range_query'] = $searchModel->hasRangeQuery();
        }

        return $response;
    }

    public function actionSearch()
    {
        SV_WordCountSearch_Globals::$SearchController = $this;
        return parent::actionSearch();
    }
}

// ******************** FOR IDE AUTO COMPLETE ********************
if (false)
{
    class XFCP_SV_WordCountSearch_XenForo_ControllerPublic_Search extends XenForo_ControllerPublic_Search {}
}