<?php

include 'SolrSearch/DbPager.php';

class SolrSearch_IndexAll extends ProcessAbstract
{
	public function run($args)
	{
		$solr  = new Apache_Solr_Service(SOLR_SERVER, SOLR_PORT, SOLR_CORE);
		$db    = get_db();
        $sql   = $db
            ->getTable('Item')
            ->where('public=1')
            ->order('id');
        $pager = new SolrSearch_DbPager($db, $sql);

        while ($items = $pager->next()) {
            foreach ($items as $item) {
                //define docs
                $docs = array();

                $doc = new Apache_Solr_Document();
                $doc->id = $item['id'];

                $elementTexts = $db
                    ->getTable('ElementText')
                    ->findBySql('record_id = ?', array($item['id']));
                foreach ($elementTexts as $elementText) {
                    $fieldName = $elementText['element_id'] . '_s';
                    $doc->setMultiValue($fieldName, $elementText['text']);
                    if ($elementText['element_id'] == 50) {
                        $doc->setMultiValue('title', $elementText['text']);
                    }
                }

                foreach($item->Tags as $key => $tag) {
                    $doc->setMultiValue('tag', $tag);
                }

                if ($item['collection_id'] > 0) {
                    $collectionName = $db
                        ->getTable('Collection')
                        ->find($item['collection_id'])
                        ->name;
                    $doc->collection = $collectionName;
                }

                //add item type
                if ($item['item_type_id'] > 0) {
                    $itemType = $db
                        ->getTable('ItemType')
                        ->find($item['item_type_id'])
                        ->name;
                    $doc->itemtype = $itemType;
                }

                //add images or index XML files
                $files = $item->Files;
                foreach ($files as $file) {

                    $mimeType = $file->mime_browser;
                    if($file['has_derivative_image'] == 1) {
                        $doc->setMultiValue('image', $file['id']);
                    }

                    //if the file is XML, index the full text
                    if ($mimeType == 'application/xml' || $mimeType == 'text/xml') {
                        $teiFile = $file->getPath('archive');
                        $xml_doc = new DomDocument;
                        $xml_doc->load($teiFile);
                        $xpath = new DOMXPath($xml_doc);
                        $nodes = $xpath->query('//text()');
                        foreach ($nodes as $node) {
                            $value = preg_replace('/\s\s+/', ' ', trim($node->nodeValue));
                            if ($value != ' ' && $value != '') {
                                $doc->setMultiValue('fulltext', $value);
                            }
                        }
                    }
                }
                //if FedoraConnector is installed, index fulltext of XML
                if (function_exists('fedora_connector_installed')) {
                    $datastreams = $db->getTable('FedoraConnector_Datastream')->findBySql('mime_type = ? AND item_id = ?', array('text/xml', $item->id));
                    foreach($datastreams as $datastream) {
                        $teiFile = fedora_connector_content_url($datastream);
                        $fedora_doc = new DomDocument;
                        $fedora_doc->load($teiFile);
                        $xpath = new DOMXPath($fedora_doc);
                        $nodes = $xpath->query('//text()');
                        foreach ($nodes as $node) {
                            $value = preg_replace('/\s\s+/', ' ', trim($node->nodeValue));
                            if ($value != ' ' && $value != '') {
                                $doc->setMultiValue('fulltext', $value);
                            }
                        }
                    }
                }

                //add docs to array to be posted to Solr
                $docs[] = $doc;
                $solr->addDocuments($docs);
            }
        }
		try {
	    	$solr->commit();
			$solr->optimize();
		}
		catch ( Exception $e ) {
			echo $e->getMessage();
		}
	}
}
