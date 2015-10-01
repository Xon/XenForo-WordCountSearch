<?php

class SV_SearchImprovements_XenForo_Model_Search extends XFCP_SV_SearchImprovements_XenForo_Model_Search
{
    function str_word_count_utf8($str)
    {
        // ref: http://php.net/manual/de/function.str-word-count.php#107363
        return count(preg_split('~[^\p{L}\p{N}\']+~u',$str));
    }

    public function getTextWordCount($message)
    {
        return $this->str_word_count_utf8(XenForo_Helper_String::bbCodeStrip($message, true));
    }

    public function roundWordCount($WordCount)
    {
        $ApproximateWordCount = $WordCount;
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

    public function getGeneralConstraintsFromInput(array $input, &$errors = null)
    {
        $constraints = parent::getGeneralConstraintsFromInput($input, $errors);

        if (empty(SV_SearchImprovements_Globals::$SearchController))
        {
            return $constraints;
        }

        $input2 = SV_SearchImprovements_Globals::$SearchController->getInput()->filter(array(
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
}

