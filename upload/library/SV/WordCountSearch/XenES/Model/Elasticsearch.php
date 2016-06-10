<?php

class SV_WordCountSearch_XenES_Model_ElasticSearch extends XFCP_SV_WordCountSearch_XenES_Model_ElasticSearch
{
	public function getOptimizableMappings(array $mappingTypes = null, $mappings = null)
	{
		if ($mappingTypes === null)
		{
			$mappingTypes = $this->getAllSearchContentTypes();
		}
		if ($mappings === null)
		{
			$mappings = $this->getMappings();
		}
        return $this->_getOptimizableMappings($mappingTypes, $mappings, []);
	}

	protected function _getOptimizableMappings(array $mappingTypes, $mappings, $extraMappings)
	{
        $extraMappings = array_merge($extraMappings, SV_WordCountSearch_Installer::$extraMappings);
        if (is_callable('parent::_getOptimizableMappings'))
        {
            return parent::_getOptimizableMappings($mappingTypes, $mappings, $extraMappings);
        }
        return SV_Utils_ElasticSearch::getOptimizableMappings($mappingTypes, $mappings, $extraMappings);
    }

    public function optimizeMapping($type, $deleteFirst = true, array $extra = array())
    {
        if (isset(SV_WordCountSearch_Installer::$extraMappings[$type]))
        {
            $extra = array_merge($extra, SV_WordCountSearch_Installer::$extraMappings[$type]);
        }

        parent::optimizeMapping($type, $deleteFirst, $extra);
    }
}