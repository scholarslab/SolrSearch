<?php
/**
 * SolrSearch Omeka Plugin helpers.
 *
 * Default helpers for the SolrSearch plugin
 *
 * Licensed under the Apache License, Version 2.0 (the "License"); you may not
 * use this file except in compliance with the License. You may obtain a copy of
 * the License at http://www.apache.org/licenses/LICENSE-2.0 Unless required by
 * applicable law or agreed to in writing, software distributed under the
 * License is distributed on an "AS IS" BASIS, WITHOUT WARRANTIES OR CONDITIONS
 * OF ANY KIND, either express or implied. See the License for the specific
 * language governing permissions and limitations under the License.
 *
 * @package    omeka
 * @subpackage SolrSearch
 * @author     "Scholars Lab"
 * @copyright  2010 The Board and Visitors of the University of Virginia
 * @license    http://www.apache.org/licenses/LICENSE-2.0 Apache 2.0
 * @version    $Id$
 * @link       http://www.scholarslab.org
 *
 * PHP version 5
 *
 */


/**
 * This class handles parsing config files into SolrSearch_Addon_* classes.
 **/
class SolrSearch_Addon_Config
{

    // {{{Properties

    /**
     * The database this will interface with.
     *
     * @var Omeka_Db
     **/
    var $db;

    // }}}

    // {{{Public Methods

    function __construct($db)
    {
        $this->db = $db;
    }

    /**
     * This parses a string into an associative array of SolrSearch_Addon_Addon 
     * classes.
     *
     * @param string $configJson The input JSON configuration to parse.
     *
     * @return array of SolrSearch_Addon_Addon
     * @author Eric Rochester <erochest@virginia.edu>
     **/
    public function parseString($input)
    {
        $addons = array();
        $json   = json_decode($input, TRUE);

        if ($json !== null) {
            $addons = $this->parseAddonCollection($json);
        }

        return $addons;
    }

    /**
     * This parses the JSON data in a file.
     *
     * @param string $filename The file name to parse.
     *
     * @return array of SolrSearch_Addon_Addon
     * @author Eric Rochester <erochest@virginia.edu>
     **/
    public function parseFile($filename)
    {
        return $this->parseString(file_get_contents($filename));
    }

    /**
     * This parses a directory of JSON files.
     *
     * @param string $dirname The directory containing JSON files to parse.
     *
     * @return array of SolrSearch_Addon_Addon
     * @author Eric Rochester <erochest@virginia.edu>
     **/
    public function parseDir($dirname)
    {
        $addons = array();

        if ($d = opendir($dirname)) {
            while (($file = readdir($d)) !== false) {
                if (preg_match('/\.json$/i', $file)) {
                    $a = $this->parseFile("$dirname/$file");
                    $addons = array_merge($addons, $a);
                }
            }
            closedir($d);
        }

        return $addons;
    }

    // }}}

    // {{{Private Methods

    /**
     * This takes a JSON object describing an addon and parses it into an 
     * Addon.
     *
     * @param string $json The JSON object to parse.
     * @param SolrSearch_Addon_Addon|null $parent The parent for the objects in 
     * this collection.
     *
     * @return array of SolrSearch_Addon_Addon
     * @author Eric Rochester <erochest@virginia.edu>
     **/
    private function parseAddonCollection($json, $parent=null)
    {
        $addons = array();

        foreach ($json as $name => $jAddon) {
            $addon = $this->parseAddon(
                $name, $jAddon, $parent
            );

            if ($addon !== null) {
                $addons = array_merge($addons, $addon);
            }
        }

        return $addons;
    }

    /**
     * This parses the data for a single addon.
     *
     * @param string $name The name of the addon.
     * @param array $json An associate array representing the JSON object for 
     * the addon.
     * @param SolrSearch_Addon_Addon|null $parent The parent for the object.
     *
     * @return array of SolrSearch_Addon_Addon
     * @author Eric Rochester <erochest@virginia.edu>
     **/
    private function parseAddon($name, $json, $parent=null)
    {
        $addon  = new SolrSearch_Addon_Addon($name);
        $addons = array( $name => $addon );

        if (array_key_exists('result_type', $json)) {
            $addon->resultType = $json['result_type'];
        }
        if (array_key_exists('table', $json)) {
            $addon->table = $json['table'];
        }
        if (array_key_exists('id_column', $json)) {
            $addon->idColumn = $json['id_column'];
        }
        $addon->parentAddon = $parent;
        if (array_key_exists('parent_key', $json)) {
            $addon->parentKey = $json['parent_key'];
        }
        if (array_key_exists('tagged', $json)) {
            $addon->tagged = $json['tagged'];
        }
        if (array_key_exists('flag', $json)) {
            $addon->flag = $json['flag'];
        }

        if (array_key_exists('fields', $json)) {
            $addon->fields = array();
            foreach ($json['fields'] as $field) {
                $addon->fields[] = $this->parseField($field);
            }
        }

        if (array_key_exists('children', $json)) {
            $children = $this->parseAddonCollection(
                $json['children'], $addon
            );
            foreach ($children as $cname => $child) {
                $addon->children[] = $child;
                $addons[$cname] = $child;
            }
        }

        return $addons;
    }

    /**
     * This parses a JSON object or string into a SolrSearch_Addon_Field.
     * 
     * @param array|string $json The JSON object to parse into a field.
     *
     * @return SolrSearch_Addon_Field $field That parsed field.
     * @author Eric Rochester <erochest@virginia.edu>
     **/
    private function parseField($json)
    {
        $field = new SolrSearch_Addon_Field();

        if (is_array($json)) {
            $field->name     = $json['field'];
            $field->label    = $json['label'];
            $field->is_facet = array_key_exists('facet', $json)    ? $json['facet']    : false;
            $field->is_title = array_key_exists('is_title', $json) ? $json['is_title'] : false;
            $field->remote   = array_key_exists('remote', $json)   ? (object) $json['remote'] : null;
        } else {
            $field->name     = $json;
            $field->label    = $json;
            $field->is_facet = false;
            $field->is_title = false;
            $field->remote   = null;
        }

        return $field;
    }

    // }}}

}

/*
 * Local variables:
 * tab-width: 4
 * c-basic-offset: 4
 * c-hanging-comment-ender-p: nil
 * End:
 */
