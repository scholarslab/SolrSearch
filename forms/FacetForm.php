<?php
/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * Facet form.
 *
 * PHP version 5
 *
 * Licensed under the Apache License, Version 2.0 (the "License"); you may not
 * use this file except in compliance with the License. You may obtain a copy of
 * the License at http://www.apache.org/licenses/LICENSE-2.0 Unless required by
 * applicable law or agreed to in writing, software distributed under the
 * License is distributed on an "AS IS" BASIS, WITHOUT WARRANTIES OR CONDITIONS
 * OF ANY KIND, either express or implied. See the License for the specific
 * language governing permissions and limitations under the License.
 *
 * @package     omeka
 * @subpackage  SolrSearch
 * @author      Scholars' Lab <>
 * @author      David McClure <david.mcclure@virginia.edu>
 * @copyright   2012 The Board and Visitors of the University of Virginia
 * @license     http://www.apache.org/licenses/LICENSE-2.0.html Apache 2 License
 */

class FacetForm extends Omeka_Form
{

    /**
     * Construct the exhibit add/edit form.
     *
     * @return void.
     */
    public function init()
    {

        // Get facets table.
        $_db = get_db();
        $_facetsTable = $_db->getTable('SolrSearchFacet');

        // Set form parameters.
        $this->setMethod('post');
        $this->setAction('update');
        $this->setAttrib('id', 'facets-form');

        // Get grouped facets.
        $groups = $_facetsTable->groupByElementSet();

        // Walk facet groups.
        foreach ($groups as $title => $group) {

            // Bucket group elements.
            $displayGroup = array();

            foreach ($group as $facet) {

                // Build element name.
                $inputName = 'options_' . $facet->id;
                array_push($displayGroup, $inputName);

                // Construct values array.
                $values = array();
                foreach (array('is_displayed', 'is_facet') as $key) {
                    if ($facet->$key == 1) {
                        array_push($values, $key);
                    }
                }

                // Add element.
                $this->addElement(
                    'MultiCheckbox',
                    $inputName,
                    array(
                        'label' => $facet->label,
                        'multiOptions' => array(
                            'is_displayed' => 'Is Searchable',
                            'is_facet'     => 'Is Facet'
                        ),
                    'value' => $values
                    )
                );

            }

            // Group the set fields.
            $this->addDisplayGroup(
                $displayGroup,
                $title,
                array (
                    'legend' => $title
                )
            );

        }

        // Submit.
        $this->addElement(
            'submit',
            'submit',
            array(
                'label' => 'Update Search Fields'
            )
        );

    }

}
