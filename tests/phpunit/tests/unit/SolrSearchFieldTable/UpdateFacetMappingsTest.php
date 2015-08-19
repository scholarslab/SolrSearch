<?php

/**
 * @package     omeka
 * @subpackage  solr-search
 * @copyright   2012 Rector and Board of Visitors, University of Virginia
 * @license     http://www.apache.org/licenses/LICENSE-2.0.html
 */


class SolrSearchFieldTableTest_UpdateFacetMappings
    extends SolrSearch_Case_Default
{

    public function setUp()
    {
        parent::setUp();

        $this->elSet = new ElementSet();
        $this->elSet->record_type = "item";
        $this->elSet->name = "FacetMappingsMissing";
        $this->elSet->save();

        $elementSet = $this->db->getTable('ElementSet')->findByName('Dublin Core');
        $sql = "
            INSERT INTO `{$this->db->Element}` (`element_set_id`, `name`, `description`)
            VALUES (?, ?, ?)";
        $this->db->query($sql, array($this->elSet->id, 'element one', 'description one'));

        $elementTable = $this->db->getTable('Element');
        $this->el = $elementTable->findByElementSetNameAndElementName(
            $this->elSet->name, 'element one'
        );
    }

    public function tearDown()
    {
        parent::tearDown();

        if (! is_null($this->el)) {
            $this->el->delete();
        }
        if (! is_null($this->elSet)) {
            $this->elSet->delete();
        }
    }

    /**
     * Newly added elements are not automatically added to the facet set.
     */
    public function testUpdateFacetMappingsMissing()
    {
        $this->assertNull(
            $this->fieldTable->findByElementName(
                "FacetMappingsMissing", "element one"
            ));
    }

    /**
     * Updating the facet set adds newly created elements.
     */
    public function testUpdateFacetMappings()
    {
        $this->fieldTable->updateFacetMappings();
        $this->assertNotNull(
            $this->fieldTable->findByElementName(
                "FacetMappingsMissing", "element one"
            ));
    }

    /**
     * Updating the facet set also removes orphaned facets.
     */
    public function testRemoveRemovedFacets()
    {
        $this->fieldTable->updateFacetMappings();
        $facet = $this->fieldTable->findByElementName(
            "FacetMappingsMissing", "element one"
        );
        $this->assertNotNull($facet);

        if (! is_null($this->el)) {
            $sql = "DELETE FROM `{$this->db->Element}` WHERE id=?;";
            $this->db->query($sql, array($this->el->id));
            $this->el = null;
        }

        $this->fieldTable->updateFacetMappings();
        $facet2 = $this->fieldTable->findBySql(
            'label=?', array($facet->label), true
        );
        $this->assertNull($facet2);
    }

}
