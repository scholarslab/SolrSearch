<?php

/**
 * @package     omeka
 * @subpackage  solr-search
 * @copyright   2012 Rector and Board of Visitors, University of Virginia
 * @license     http://www.apache.org/licenses/LICENSE-2.0.html
 */


class SolrSearchFieldTable extends Omeka_Db_Table
{


    /**
     * Find the field associated with a given element text.
     *
     * @param ElementText $text The element text.
     * @return SolrSearchField
     */
    public function findByText($text)
    {
        return $this->findBySql(
            'element_id=?', array($text->element_id), true
        );
    }


    /**
     * Find the field associated with a given element.
     *
     * @param Element $element The element.
     * @return SolrSearchField
     */
    public function findByElement($element)
    {
        return $this->findBySql(
            'element_id=?', array($element->id), true
        );
    }


    /**
     * Find the field associated with a given element, identified by element
     * set name and element name.
     *
     * @param string $set The element set name.
     * @param string $element The element name.
     * @return SolrSearchField
     */
    public function findByElementName($set, $element)
    {

        // Get the element table.
        $elementTable = $this->getTable('Element');

        // Get the parent element.
        $element = $elementTable->findByElementSetNameAndElementName(
            $set, $element
        );

        // Find the element's field.
        return $this->findByElement($element);

    }


    /**
     * Find the facet with a given slug.
     *
     * @param string $slug The slug.
     * @return SolrSearchField
     */
    public function findBySlug($slug)
    {
        return $this->findBySql('slug=?', array($slug), true);
    }


    /**
     * Flag a metadata element to be indexed in Solr.
     *
     * @param string $set The element set name.
     * @param string $element The element name.
     * @param boolean $value True if indexed.
     */
    public function setElementIndexed($set, $element, $value = true) {
        $this->setElementFlag($set, $element, 'is_indexed', $value);
    }


    /**
     * Flag a metadata element to be used as a facet.
     *
     * @param string $set The element set name.
     * @param string $element The element name.
     * @param boolean $value True if faceted.
     */
    public function setElementFaceted($set, $element, $value = true) {
        $this->setElementFlag($set, $element, 'is_facet', $value);
    }


    /**
     * Flip a boolean flag on an element-backed field.
     *
     * @param string $set The element set name.
     * @param string $element The element name.
     * @param string $flag The name of the flag.
     * @param boolean $value True if on.
     */
    public function setElementFlag($set, $element, $flag, $value = true) {
        $field = $this->findByElementName($set, $element);
        $field->$flag = $value;
        $field->save();
    }


    /**
     * Get a list of the slugs of all active facets.
     *
     * @return array The list of active facet slugs.
     */
    public function getActiveFacetKeys()
    {

        $active = array();

        // Get names for active facets.
        foreach ($this->findBySql('is_facet=?', array(1)) as $field) {
            $active[] = $field->facetKey();
        }

        return $active;

    }


    /**
     * Get all fields grouped by element set id.
     *
     * @return array $facets The ElementSet-grouped facets.
     */
    public function groupByElementSet()
    {

        $groups = array();

        foreach ($this->findAll() as $facet) {

            // Get element set name.
            $set = $facet->getElementSetName();

            // Add the facet to its element set group (or create it).
            if (array_key_exists($set, $groups)) $groups[$set][] = $facet;
            else $groups[$set] = array($facet);

        }

        return $groups;

    }


}
