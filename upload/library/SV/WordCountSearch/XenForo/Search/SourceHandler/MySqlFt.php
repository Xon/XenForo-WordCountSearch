<?php

class SV_WordCountSearch_XenForo_Search_SourceHandler_MySqlFt extends XFCP_SV_WordCountSearch_XenForo_Search_SourceHandler_MySqlFt
{
    public function executeSearch($searchQuery, $titleOnly, array $processedConstraints, array $orderParts,
        $groupByDiscussionType, $maxResults, XenForo_Search_DataHandler_Abstract $typeHandler = null
    )
    {
        $db = $this->_getDb();

        $queryParts = $this->tokenizeQuery($searchQuery);
        $searchQuery = $this->parseTokenizedQuery($queryParts, $processedConstraints);

        if ($titleOnly)
        {
            $matchFields = 'search_index.title, search_index.metadata';
        }
        else
        {
            $matchFields = 'search_index.title, search_index.message, search_index.metadata';
        }

        $tables = array();
        $whereClauses = array();
        $leftJoins = array();

        foreach ($processedConstraints AS $constraint)
        {
            if (isset($constraint['query']) && !isset($constraint['metadata']))
            {
                // pull queries without metadata alternatives
                list($queryTable, $queryField, $queryOperator, $queryValues) = $constraint['query'];

                if (is_array($queryValues) && count($queryValues) == 0)
                {
                    continue;
                }

                if ($queryOperator == '=' && is_array($queryValues))
                {
                    $whereClauses[] = "$queryTable.$queryField IN (" . $db->quote($queryValues) . ")";
                }
                else
                {
                    if (!is_scalar($queryValues))
                    {
                        $queryValues = strval($queryValues);
                    }
                    $whereClauses[] = "$queryTable.$queryField $queryOperator " . $db->quote($queryValues);
                }

                $tables[] = $queryTable;
            }
            else if (isset($constraint['range_query']))
            {
                list($queryField, $minPart, $maxPart) = $constraint['range_query'];
                
                if (!$leftJoins)
                {
                    $leftJoins[] = "left join xf_thread on (search_index.content_type = 'thread' and search_index.content_id = xf_thread.thread_id)";
                    $leftJoins[] = "left join xf_post_words on (search_index.content_type = 'post' and search_index.content_id = xf_post_words.post_id)";
                }
                
                $orParts = array();
                if (isset($minPart[0]) && isset($minPart[1]))
                {
                    $minPart[1] = intval($minPart[1]);
                    $orParts[] = "(xf_thread.{$queryField} {$minPart[0]} {$minPart[1]} OR xf_post_words.{$queryField} {$minPart[0]} {$minPart[1]})";
                }
                if (isset($maxPart[0]) && isset($maxPart[1]))
                {
                    $maxPart[1] = intval($maxPart[1]);
                    $orParts[] = "(xf_thread.{$queryField} {$maxPart[0]} {$maxPart[1]} OR xf_post_words.{$queryField} {$maxPart[0]} {$maxPart[1]})";
                }
                if ($orParts)
                {
                    $sql = join(' OR ', $orParts);
                    $whereClauses[] = $sql;
                }
            }
        }

        $orderFields = array();
        foreach ($orderParts AS $order)
        {
            list($orderTable, $orderField, $orderDirection) = $order;

            $orderFields[] = "$orderTable.$orderField $orderDirection";
            $tables[] = $orderTable;
        }
        $orderClause = ($orderFields ? 'ORDER BY ' . implode(', ', $orderFields) : 'ORDER BY NULL');

        $tables = array_flip($tables);
        unset($tables['search_index']);
        if ($typeHandler)
        {
            $joinStructures = $typeHandler->getJoinStructures($tables);
            $joins = array();
            foreach ($joinStructures AS $tableAlias => $joinStructure)
            {
                list($relationshipTable, $relationshipField) = $joinStructure['relationship'];
                $joins[] = "INNER JOIN $joinStructure[table] AS $tableAlias ON
                    ($tableAlias.$joinStructure[key] = $relationshipTable.$relationshipField)";
            }
        }
        else
        {
            $joins = array();
        }

        $extraWhere = ($whereClauses ? 'AND (' . implode(') AND (', $whereClauses) . ')' : '');

        if ($groupByDiscussionType)
        {
            $selectFields = $db->quote($groupByDiscussionType) . ' AS content_type, search_index.discussion_id AS content_id';
            $groupByClause = 'GROUP BY search_index.discussion_id';
        }
        else
        {
            $selectFields = 'search_index.content_type, search_index.content_id';
            $groupByClause = '';
        }

        if ($maxResults < 1)
        {
            $maxResults = 100;
        }
        $maxResults = intval($maxResults);

        if ($this->_searcher && $this->_searcher->hasErrors())
        {
            return array();
        }

        return $db->fetchAll("
            SELECT $selectFields
            FROM xf_search_index AS search_index
            " . implode("\n", $leftJoins) . "
            " . implode("\n", $joins) . "
            WHERE MATCH($matchFields) AGAINST (? IN BOOLEAN MODE)
                $extraWhere
            $groupByClause
            $orderClause
            LIMIT $maxResults
        ", $searchQuery, Zend_Db::FETCH_NUM);
        //return parent::executeSearch($searchQuery, $titleOnly, $processedConstraints, $orderParts, $groupByDiscussionType, $maxResults, $typeHandler);
    }

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
    class XFCP_SV_WordCountSearch_XenForo_Search_SourceHandler_MySqlFt extends XenForo_Search_SourceHandler_MySqlFt {}
}