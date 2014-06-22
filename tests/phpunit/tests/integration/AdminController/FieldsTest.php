<?php

/**
 * @package     omeka
 * @subpackage  solr-search
 * @copyright   2012 Rector and Board of Visitors, University of Virginia
 * @license     http://www.apache.org/licenses/LICENSE-2.0.html
 */

class AdminControllerTest_Fields extends SolrSearch_Case_Default
{


    /**
     * FIELDS should display configuration rows for each of the facets.
     */
    public function testFormMarkup()
    {

        $this->dispatch('solr-search/fields');

        foreach ($this->fieldTable->findAll() as $facet) {

            // Label:
            $this->assertXpath(
                "//input[@name='facets[{$facet->slug}][label]']
                [@value='{$facet->label}']"
            );

            // ID:
            $this->assertXpath(
                "//input[@name='facets[{$facet->slug}][id]']
                [@value='{$facet->id}']"
            );

            // Is Indexed?:
            $this->assertXpath(
                "//input[@name='facets[{$facet->slug}][is_indexed]']" .
                ($facet->is_indexed ? "[@checked='checked']" : "")
            );

            // Is Facet?:
            $this->assertXpath(
                "//input[@name='facets[{$facet->slug}][is_facet]']" .
                ($facet->is_facet ? "[@checked='checked']" : "")
            );

        }

    }


    /**
     * FIELDS should save the field labels.
     */
    public function testSetLabel()
    {

        foreach ($this->fieldTable->findAll() as $facet) {

            $newLabel = $facet->label.'-changed';

            // Set an updated label.
            $this->request->setMethod('POST')->setPost(array(
                'facets' => array(
                    "{$facet->slug}" => array(
                        'id'    => $facet->id,
                        'label' => $newLabel
                    )
                )
            ));

            $this->dispatch('solr-search/fields');
            $facet = $this->_reload($facet);

            // Should save the label.
            $this->assertEquals($newLabel, $facet->label);

        }

    }


    /**
     * FIELDS should save the "Is Indexed?" and "Is Facet?" flags.
     */
    public function testSetFlags()
    {

        foreach ($this->fieldTable->findAll() as $facet) {

            $data = array('id' => $facet->id, 'label' => $facet->label);

            // Get the opposite values for flags.
            $newIndexed = $facet->is_indexed ? 0 : 1;
            $newFaceted = $facet->is_facet ? 0 : 1;

            // Flip on "Is Indexed?".
            if ($newIndexed) $data = array_merge($data, array(
                'is_indexed' => 'on'
            ));

            // Flip on "Is Facet?".
            if ($newFaceted) $data = array_merge($data, array(
                'is_facet' => 'on'
            ));

            // Set the updated flags.
            $this->request->setMethod('POST')->setPost(array(
                'facets' => array(
                    "{$facet->slug}" => $data
                )
            ));

            $this->dispatch('solr-search/fields');
            $facet = $this->_reload($facet);

            // Should save "Is Indexed?".
            $this->assertEquals($newIndexed, $facet->is_indexed);

            // Should save "Is Facet?".
            $this->assertEquals($newFaceted, $facet->is_facet);

        }

    }


}
