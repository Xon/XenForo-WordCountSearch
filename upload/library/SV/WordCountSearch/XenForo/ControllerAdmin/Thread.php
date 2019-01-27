<?php

class SV_WordCountSearch_XenForo_ControllerAdmin_Thread extends XFCP_SV_WordCountSearch_XenForo_ControllerAdmin_Thread
{
    protected function _filterThreadSearchCriteria(array $criteria)
    {
        $criteria = parent::_filterThreadSearchCriteria($criteria);

        if (isset($criteria['lword']) && intval($criteria['lword']) <= 0)
        {
            unset($criteria['lword']);
        }

        if (isset($criteria['uword']) && intval($criteria['uword']) <= 0)
        {
            unset($criteria['uword']);
        }


        return $criteria;
    }
}

// ******************** FOR IDE AUTO COMPLETE ********************
if (false)
{
    class XFCP_SV_WordCountSearch_XenForo_ControllerAdmin_Thread extends XenForo_ControllerAdmin_Thread {}
}