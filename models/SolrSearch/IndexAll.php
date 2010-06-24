<?php

class SolrSearch_IndexAll extends ProcessAbstract 
{ 
	public function run($args) 
	{		
		$solr = new Apache_Solr_Service(SOLR_SERVER, SOLR_PORT, SOLR_CORE);
		
		$db = get_db();
		$items = $db->getTable('Item')->findAll();

		//define docs
		$docs = array();
		
		foreach ($items as $item){
			$elementTexts = $item->getTable('ElementText')->findBySql('record_id = ?', array($item['id']));	
			$doc = new Apache_Solr_Document();
			$doc->id = $item['id'];
			foreach ($elementTexts as $elementText){
				$fieldName = $elementText['element_id'] . '_s';
				$doc->setMultiValue($fieldName, $elementText['text']);
			}
			$docs[] = $doc;
		}
		try {
	    	$solr->addDocuments($docs);
			$solr->commit();
			$solr->optimize();
		}
		catch ( Exception $e ) {
			echo $e->getMessage();
		}
	}
}