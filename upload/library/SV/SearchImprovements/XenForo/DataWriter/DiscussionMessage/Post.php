<?php

class SV_SearchImprovements_XenForo_DataWriter_DiscussionMessage_Post extends XFCP_SV_SearchImprovements_XenForo_DataWriter_DiscussionMessage_Post
{
    protected function _getFields()
    {
        $fields = parent::_getFields();
        $fields['xf_post_words'] = array
        (
            'post_id' => array('type' => self::TYPE_UINT,   'default' => array('xf_post', 'post_id'), 'required' => true),
            'word_count' => array('type' => self::TYPE_UINT, 'default' => 0)
        );
        return $fields;
    }

    protected function _messagePreSave()
    {
        parent::_messagePreSave();
        if ($this->isChanged('message'))
        {
            $this->set('word_count', $this->_getSearchModel()->getTextWordCount($this->get('message')));
        }
    }

    protected function _getSearchModel()
    {
        return $this->getModelFromCache('XenForo_Model_Search');
    }
}
