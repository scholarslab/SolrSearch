<?php

/**
 * @package     omeka
 * @subpackage  solr-search
 * @copyright   2012 Rector and Board of Visitors, University of Virginia
 * @license     http://www.apache.org/licenses/LICENSE-2.0.html
 */


class SolrSearchAddonTest_Config extends SolrSearch_Case_Default
{

    private static $config_json = <<<'EOF'

{
    "exhibits": {
        "id_column": "eid",
        "tagged": true,
        "fields": [
            "title",
            "description",
            {
                "facet": true,
                "label": "Featured Exhibits",
                "field": "featured"
            }
        ],
        "children": {
            "sections": {
                "parent_key": "exhibit_id",
                "fields": [
                    { "field": "name", "label": "Name", "is_title": true },
                    "description",
                    {
                        "field": "pagetitle",
                        "label": "Page Title",
                        "facet": false,
                        "is_title": false,
                        "remote": {
                            "table": "ExhibitPageBlock",
                            "key": "page_id"
                        }
                    }
                ],
                "table": "Sections"
            }
        },
        "flag": "public",
        "table": "Exhibits",
        "result_type": "Exhibits"
    }
}

EOF;

    private function assertAddon(
        $addon, $name, $resultType, $table, $idCol, $parent, $parentKey,
        $tagged, $flag, $fieldCount, $childCount
    ) {
        $this->assertEquals($name, $addon->name);
        $this->assertEquals($resultType, $addon->resultType);
        $this->assertEquals($table, $addon->table);
        $this->assertEquals($idCol, $addon->idColumn);
        if (is_null($parent)) {
            $this->assertNull($addon->parentAddon);
        } else {
            $this->assertEquals($parent, $addon->parentAddon->name);
        }
        $this->assertEquals($parentKey, $addon->parentKey);
        $this->assertEquals($tagged, $addon->tagged);
        $this->assertEquals($flag, $addon->flag);
        $this->assertCount($fieldCount, $addon->fields);
        $this->assertCount($childCount, $addon->children);
    }

    private function assertField(
        $field, $name, $label, $facet, $title, $remote=null
    ) {
        $this->assertEquals($name, $field->name);
        $this->assertEquals($label, $field->label);
        $this->assertEquals($facet, $field->is_facet);
        $this->assertEquals($title, $field->is_title);

        if (is_null($remote)) {
            $this->assertNull($field->remote);
        } else {
            $this->assertEquals($remote['table'], $field->remote->table);
            $this->assertEquals($remote['key'],   $field->remote->key);
        }
    }

    /**
     * This tests parsing a config file to a a SolrSearch_Addon.
     **/
    public function testParse()
    {

        $config = new SolrSearch_Addon_Config($this->db);
        $addons = $config->parseString(self::$config_json);

        $this->assertCount(2, $addons);
        $this->assertArrayHasKey('exhibits', $addons);
        $this->assertArrayHasKey('sections', $addons);

        // EXHIBITS
        // --------------------------------------------------------------------

        $a = $addons['exhibits'];

        $this->assertAddon(
            $a, 'exhibits', 'Exhibits', 'Exhibits', 'eid', null, null,
            true, 'public', 3, 1
        );

        $this->assertField(
            $a->fields[0], 'title', 'title', false, false
        );

        $this->assertField(
            $a->fields[1], 'description', 'description', false, false
        );

        $this->assertField(
            $a->fields[2], 'featured', 'Featured Exhibits', true, false
        );

        $this->assertEquals('sections', $a->children[0]->name);

        // SECTIONS
        // --------------------------------------------------------------------

        $a = $addons['sections'];

        $this->assertAddon(
            $a, 'sections', null, 'Sections', 'id', 'exhibits', 'exhibit_id',
            false, null, 3, 0
        );

        $this->assertField(
            $a->fields[0], 'name', 'Name', false, true
        );

        $this->assertField(
            $a->fields[1], 'description', 'description', false, false
        );

        $this->assertField(
            $a->fields[2], 'pagetitle', 'Page Title', false, false,
            array( 'table' => 'ExhibitPageBlock', 'key' => 'page_id' )
        );

    }

}

