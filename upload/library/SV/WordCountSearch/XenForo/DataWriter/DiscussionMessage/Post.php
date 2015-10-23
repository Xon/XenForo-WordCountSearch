<?php

class SV_WordCountSearch_XenForo_DataWriter_DiscussionMessage_Post extends XFCP_SV_WordCountSearch_XenForo_DataWriter_DiscussionMessage_Post
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

    protected function _getExistingData($data)
    {
        $postData = parent::_getExistingData($data);
        if (isset($postData['xf_post']['word_count']))
        {
            $postData['xf_post_words'] = array('post_id' => $postData['xf_post']['post_id'], 'word_count' => $postData['xf_post']['word_count']);
            unset($postData['xf_post']['word_count']);
        }
        return $postData;
    }

    protected function _messagePostDelete()
    {
        parent::_messagePostDelete();
        $db = $this->_db;
        $post_id = $this->get('post_id');
        $postIdQuoted = $db->quote($post_id);

        $db->delete('xf_post_words', "post_id = $postIdQuoted");
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
