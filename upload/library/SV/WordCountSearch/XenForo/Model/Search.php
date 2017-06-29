<?php

class SV_WordCountSearch_XenForo_Model_Search extends XFCP_SV_WordCountSearch_XenForo_Model_Search
{
    function str_word_count_utf8($str)
    {
        // ref: http://php.net/manual/de/function.str-word-count.php#107363
        return count(preg_split('~[^\p{L}\p{N}\']+~u',$str));
    }

    static $hasElasticSearch = null;
    static $hasMySQLSearch = null;

    public function hasRangeQuery()
    {
        if(self::$hasElasticSearch === null)
        {
            // var XenForo_Search_SourceHandler_Abstract $sourceHandler
            $sourceHandler = XenForo_Search_SourceHandler_Abstract::getDefaultSourceHandler();
            // check if it is a supported type
            self::$hasElasticSearch = class_exists('XFCP_SV_SearchImprovements_XenES_Search_SourceHandler_ElasticSearch', false);
            self::$hasMySQLSearch = class_exists('XFCP_SV_WordCountSearch_XenForo_Search_SourceHandler_MySqlFt', false);
        }
        return  self::$hasElasticSearch || self::$hasMySQLSearch;
    }

    public function pushWordCountInIndex()
    {
        if(self::$hasElasticSearch === null)
        {
            $this->hasRangeQuery();
        }
        return self::$hasElasticSearch;
    }

    public function getWordCountThreshold()
    {
        if(self::$hasElasticSearch === null)
        {
            $this->hasRangeQuery();
        }
        if (self::$hasMySQLSearch)
        {
            return 0;
        }
        return SV_WordCountSearch_Globals::$wordCountThreshold;
    }

    public function shouldRecordPostWordCount($postId, $wordCount)
    {
        if ($wordCount >= $this->getWordCountThreshold())
        {
            return true;
        }

        if ($threadmarksModel = $this->_getThreadmarksModelIfThreadmarksActive())
        {
            if ($threadmarksModel->getByPostId($postId))
            {
                return true;
            }
        }

        return false;
    }

    public function getTextWordCount($message)
    {
        return $this->str_word_count_utf8(XenForo_Helper_String::bbCodeStrip($message, true));
    }

    public function roundWordCount($WordCount)
    {
        $ApproximateWordCount = intval($WordCount);
        if (!$ApproximateWordCount)
        {
            return 0;
        }
        if ($ApproximateWordCount > 1000000)
            $ApproximateWordCount = round($ApproximateWordCount / 1000000, 1) . 'm';
        else if ($ApproximateWordCount > 100000)
            $ApproximateWordCount = round($ApproximateWordCount / 100000, 1) * 100 . 'k';
        else if ($ApproximateWordCount > 10000)
            $ApproximateWordCount = round($ApproximateWordCount / 10000, 1) * 10 . 'k';
        else if ($ApproximateWordCount > 1000)
            $ApproximateWordCount = round($ApproximateWordCount / 1000, 1) . 'k';
        else if ($ApproximateWordCount > 100)
            $ApproximateWordCount = round($ApproximateWordCount / 100, 1) * 100;
        else if ($ApproximateWordCount > 10)
            $ApproximateWordCount = round($ApproximateWordCount / 10, 1) * 10;
        else
            $ApproximateWordCount = 10;
        return $ApproximateWordCount;
    }

    protected function _getController()
    {
        if (!empty(SV_WordCountSearch_Globals::$SearchController))
        {
            return SV_WordCountSearch_Globals::$SearchController;
        }
        if (!empty(SV_SearchImprovements_Globals::$SearchController))
        {
            return SV_SearchImprovements_Globals::$SearchController;
        }
        return null;
    }

    public function getGeneralConstraintsFromInput(array $input, &$errors = null)
    {
        $constraints = parent::getGeneralConstraintsFromInput($input, $errors);

        $controller = $this->_getController();
        if (!$controller)
        {
            return $constraints;
        }

        $input2 = $controller->getInput()->filter(array(
            'word_count' => XenForo_Input::ARRAY_SIMPLE,
        ));

        if (!empty($input2['word_count']) && isset($input2['word_count']['lower']) && $input2['word_count']['lower'] !== '')
        {
            $constraints['word_count'][0] = intval($input2['word_count']['lower']);
            if ($constraints['word_count'][0] < 0)
            {
                unset($constraints['word_count'][0]);
            }
        }

        if (!empty($input2['word_count']) && isset($input2['word_count']['upper']) && $input2['word_count']['upper'] !== '')
        {
            $constraints['word_count'][1] = intval($input2['word_count']['upper']);
            if ($constraints['word_count'][1] < 0)
            {
                unset($constraints['word_count'][1]);
            }
        }

        if (empty($constraints['word_count']))
        {
            unset($constraints['word_count']);
        }

        return $constraints;
    }

	/**
	 * @return Sidane_Threadmarks_Model_Threadmarks
	 */
    protected function _getThreadmarksModelIfThreadmarksActive()
    {
        if (!SV_Utils_AddOn::addOnIsActive('sidaneThreadmarks', 1030002))
        {
            return null;
        }

        return $this->getModelFromCache('Sidane_Threadmarks_Model_Threadmarks');
    }
}

