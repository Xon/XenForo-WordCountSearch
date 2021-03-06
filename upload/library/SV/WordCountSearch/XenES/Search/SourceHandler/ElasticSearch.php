<?php

class SV_WordCountSearch_XenES_Search_SourceHandler_ElasticSearch extends XFCP_SV_WordCountSearch_XenES_Search_SourceHandler_ElasticSearch
{
    public function processConstraints(array $constraints, XenForo_Search_DataHandler_Abstract $typeHandler = null)
    {
        $processed = array();

		foreach ($constraints AS $constraint => $constraintInfo)
		{
			if ((is_array($constraintInfo) && count($constraintInfo) == 0)
				|| (is_string($constraintInfo) && $constraintInfo === '')
			)
			{
				continue;
			}

			switch ($constraint)
			{
                case 'word_count':
                    $processed[$constraint] = array(
						'range_query' => array('word_count',
                                               isset($constraintInfo[0]) ? array('>=', intval($constraintInfo[0])) : array(),
                                               isset($constraintInfo[1]) ? array('<=', intval($constraintInfo[1])) : array())
					);
                    unset($constraints['$constraint']);
                    break;
            }
        }

        return $processed + parent::processConstraints($constraints, $typeHandler);
    }

    public function getGeneralOrderClause($order)
    {
        if ($order == 'word_count')
        {
            return array(
                array('search_index', 'word_count', 'desc'),
                array('search_index', 'item_date', 'desc')
            );
        }
        return parent::getGeneralOrderClause($order);
    }
}

// ******************** FOR IDE AUTO COMPLETE ********************
if (false)
{
    class XFCP_SV_WordCountSearch_XenES_Search_SourceHandler_ElasticSearch extends XenES_Search_SourceHandler_ElasticSearch {}
}