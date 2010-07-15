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
				//store Dublin Core titles as separate fields
				if ($elementText['element_id'] == 50){
					$doc->setMultiValue('title', $elementText['text']);
				}			
			}
			
			//add tags			
			foreach($item->Tags as $key => $tag){
				$doc->setMultiValue('tag', $tag);
			}
			
			//add collection
			if ($item['collection_id'] > 0){
				$collectionName = $db->getTable('Collection')->find($item['collection_id'])->name;
				$doc->collection = $collectionName;
			}
			
			//add images
			$files = $db->getTable('File')->findBySql('item_id = ?', array($item['id']));
			foreach ($files as $file){
				if($file['has_derivative_image'] == 1){
					$doc->setMultiValue('image', $file['id']);
				}
			}
			
			//add docs to array to be posted to Solr
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