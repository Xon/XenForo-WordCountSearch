<?php

class SV_SearchImprovements_XenES_Search_SourceHandler_ElasticSearch extends XFCP_SV_SearchImprovements_XenES_Search_SourceHandler_ElasticSearch
{
    protected function _processConstraint(array &$dsl, $constraintName, array $constraint)
    {
        if (isset($constraint['range_query']))
        {
            return $this->_processRangeQueryConstraint($dsl, $constraintName, $constraint);
        }
        return parent::_processConstraint($dsl, $constraintName, $constraint);
    }

    protected function _processRangeQueryConstraint(array &$dsl, $constraintName, array $constraint)
    {
        $params = array();

        if (empty($constraint[0]))
        {
            return false;
        }
        $field = $constraint[0];

        if (isset($constraint[1]) && isset($constraint[1][0]) && isset($constraint[1][1]))
        {
            $arg = $constraint[1];
            $params[$this->_getRangeOperator($arg[0])] = $arg[1];
        }

        if (isset($constraint[2]) && isset($constraint[2][0]) && isset($constraint[2][1]))
        {
            $arg = $constraint[1];
            $params[$this->_getRangeOperator($arg[0])] = $arg[1];
        }

        if (empty($params))
        {
            return false;
        }

        $dsl['query']['filtered']['filter']['and'][]['range'][$field] = $params;
        return true;
    }
}