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
 * This static class handles parsing config files into SolrSearch_Addon_* 
 * classes.
 **/
class SolrSearch_Addon_Config
{

    // {{{Public Static Methods

    /**
     * This parses a string into an associative array of SolrSearch_Addon_Addon 
     * classes.
     *
     * @param string $configJson The input JSON configuration to parse.
     *
     * @return array of SolrSearch_Addon_Addon
     * @author Eric Rochester <erochest@virginia.edu>
     **/
    public static function parseString($input)
    {
        $json   = json_decode($input, TRUE);
        $addon  = SolrSearch_Addon_Config::parseAddon($json);
        $addons = SolrSearch_Addon_Config::indexAddons($addon);
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
    public static function parseFile($filename)
    {
        return SolrSearch_Addon_Config::parseString(
            file_get_contents($filename)
        );
    }

    /**
     * This parses a directory of JSON files.
     *
     * @param string $dirname The directory containing JSON files to parse.
     *
     * @return array of SolrSearch_Addon_Addon
     * @author Eric Rochester <erochest@virginia.edu>
     **/
    public static function parseDir($dirname)
    {
        $addons = array();

        if ($d = opendir($dirname)) {
            while (($file = readdir($d)) !== false) {
                if (preg_match('/\.json$/i', $file)) {
                    $a = SolrSearch_Addon_Config::parseFile($file);
                    $addons = array_merge($addons, $a);
                }
            }
            closedir($addons);
        }

        return $addons;
    }

    // }}}

    // {{{Private Static Methods

    /**
     * This walks an Addon tree and indexes them into an associate array, keyed 
     * by name.
     *
     * @param SolrSearch_Addon_Addon $addon The parent of the addon tree to 
     * index.
     *
     * @return array
     * @author Eric Rochester <erochest@virginia.edu>
     **/
    private static function indexAddons($addon)
    {
        $addons = array();
        $stack  = array();

        $stack[] = $addon;
        while (($a = array_pop($stack)) !== NULL) {
            $addons[$a->name] = $a;
            $stack = array_merge($stack, $a->children);
        }

        return $addons;
    }

    /**
     * This takes a JSON object describing an addon and parses it into an 
     * Addon.
     *
     * @param string $json The JSON object to parse.
     *
     * @return SolrSearch_Addon_Addon|null
     * @author Eric Rochester <erochest@virginia.edu>
     **/
    private static function parseAddon($json)
    {
        $addon = new SolrSearch_Addon_Addon();

        return $addon;
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
