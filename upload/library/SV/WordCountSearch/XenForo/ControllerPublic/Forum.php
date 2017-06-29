<?php

class SV_WordCountSearch_XenForo_ControllerPublic_Forum extends XFCP_SV_WordCountSearch_XenForo_ControllerPublic_Forum
{
    protected function _getDisplayConditions(array $forum)
    {
        $displayConditions = parent::_getDisplayConditions($forum);

        $displayConditions['lword'] = $this->_input->filterSingle('lword', XenForo_Input::INT);
        if (empty($displayConditions['lword']))
        {
            unset($displayConditions['lword']);
        }
        $displayConditions['uword'] = $this->_input->filterSingle('uword', XenForo_Input::INT);
        if (empty($displayConditions['uword']))
        {
            unset($displayConditions['uword']);
        }

        return $displayConditions;
    }

	/**
	 * @return SV_WordCountSearch_XenForo_Model_Search
	 */
    protected function _getSearchModel()
    {
        return $this->getModelFromCache('XenForo_Model_Search');
    }
}

