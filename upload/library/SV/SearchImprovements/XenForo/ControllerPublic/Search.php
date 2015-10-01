<?php

class SV_SearchImprovements_XenForo_ControllerPublic_Search extends XFCP_SV_SearchImprovements_XenForo_ControllerPublic_Search
{
    public function actionIndex()
    {
        $response = parent::actionIndex();

        if ($response instanceof XenForo_ControllerResponse_View && !empty($response->params['search']))
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

        return $response;
    }

    public function actionSearch()
    {
        SV_SearchImprovements_Globals::$SearchController = $this;
        return parent::actionSearch();
    }
}
