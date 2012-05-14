<?php
/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4; */

/**
 * @package     omeka
 * @subpackage  SolrSearch
 * @author      Scholars' Lab <>
 * @author      David McClure <david.mcclure@virginia.edu>
 * @copyright   2012 The Board and Visitors of the University of Virginia
 * @license     http://www.apache.org/licenses/LICENSE-2.0.html Apache 2 License
 */

class SolrSearch_AddonConfig_Test extends SolrSearch_Test_AppTestCase
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
                    "title",
                    "description"
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

    public function setUp()
    {
        $this->setUpPlugin();
    }

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

    private function assertField($field, $name, $label, $facet)
    {
        $this->assertEquals($name, $field->name);
        $this->assertEquals($label, $field->label);
        $this->assertEquals($facet, $field->is_facet);
    }

    /**
     * This tests parsing a config file to a a SolrSearch_Addon.
     *
     * @return void
     * @author Eric Rochester <erochest@virginia.edu>
     **/
    public function testParse()
    {
        $config = new SolrSearch_Addon_Config($this->db);
        $addons = $config->parseString(self::$config_json);

        $this->assertCount(2, $addons);
        $this->assertArrayHasKey('exhibits', $addons);
        $this->assertArrayHasKey('sections', $addons);

        $a = $addons['exhibits'];
        $this->assertAddon(
            $a, 'exhibits', 'Exhibits', 'Exhibits', 'eid', null, 
            null, true, 'public', 3, 1
        );
        $this->assertField($a->fields[0], 'title', 'title', false);
        $this->assertField(
            $a->fields[1], 'description', 'description', false
        );
        $this->assertField(
            $a->fields[2], 'featured', 'Featured Exhibits', true
        );
        $this->assertEquals('sections', $a->children[0]->name);

        $a = $addons['sections'];
        $this->assertAddon(
            $a, 'sections', null, 'Sections', 'id', 'exhibits',
            'exhibit_id', false, null, 2, 0
        );
        $this->assertField($a->fields[0], 'title', 'title', false);
        $this->assertField(
            $a->fields[1], 'description', 'description', false
        );
    }

    public function testFieldTitleFlag()
    {
        // TODO: This tests for setting the title field in the configuration.
        $this->assertTrue(false, 'testFieldTitleFlag');
    }

}

