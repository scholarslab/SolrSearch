<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4 cc=80; */

/**
 * @package     omeka
 * @subpackage  solr-search
 * @copyright   2012 Rector and Board of Visitors, University of Virginia
 * @license     http://www.apache.org/licenses/LICENSE-2.0.html
 */


class SolrSearchFieldTable extends Omeka_Db_Table
{


    /**
     * Find the field associated with a given element.
     *
     * @return Element $element The element.
     */
    public function findByElement($element)
    {
        return $this->findBySql('element_id=?', array($element->id), true);
    }


    /**
     * Find the facet with a given slug.
     *
     * @return Element $slug The slug.
     */
    public function findBySlug($slug)
    {
        return $this->findBySql('slug=?', array($slug), true);
    }


    /**
     * Flag a metadata element to be indexed in Solr.
     *
     * @return string $set The element set name.
     * @return string $element The element name.
     * @return boolean $value True if indexed.
     * @uses setElementFlag
     */
    public function setElementIndexed($set, $element, $value = true) {
        $this->setElementFlag($set, $element, 'is_indexed', $value);
    }


    /**
     * Flag a metadata element to be used as a facet.
     *
     * @return string $set The element set name.
     * @return string $element The element name.
     * @return boolean $value True if faceted.
     * @uses setElementFlag
     */
    public function setElementFaceted($set, $element, $value) {
        $this->setElementFlag($set, $element, 'is_facet', $value);
    }


    /**
     * Flip a boolean flag on an element-backed field.
     *
     * @return string $set The element set name.
     * @return string $element The element name.
     * @return string $flag The name of the flag.
     * @return boolean $value True if on.
     */
    public function setElementFlag($set, $element, $flag, $value = true) {

        // Get the element table.
        $elementTable = $this->getTable('Element');

        // Get the parent element.
        $element = $elementTable->findByElementSetNameAndElementName(
            $set, $element
        );

        // Get the facet, set searchable.
        $facet = $this->findByElement($element);
        $facet->$flag = $value;
        $facet->save();

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
            $key = $field->hasElement() ? $field->stringKey() : $field->slug;
            $active[] = $key;
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
