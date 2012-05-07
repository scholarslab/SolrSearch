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
 * This manages the process of getting the addon information from the config files and using 
 * them to index a document.
 **/
class SolrSearch_Addon_Manager
{
    // {{{Properties

    /**
     * The database this will interface with.
     *
     * @var Omeka_Db
     **/
    var $db;

    /**
     * The addon directory.
     *
     * @var string
     **/
    var $addonDir;

    /**
     * The parsed addons
     *
     * @var array of SolrSearch_Addon_Addon
     **/
    var $addons;

    // }}}
    // {{{Methods
    
    /**
     * This instantiates a SolrSearch_Addon_Manager
     *
     * @param Omeka_Db $db       The database to initialize everything with.
     * @param string   $addonDir The directory for the addon config files.
     **/
    function __construct($db, $addonDir=null)
    {
        $this->db       = $db;
        $this->addonDir = $addonDir;
        $this->addons   = null;

        if ($this->addonDir === null) {
            $this->addonDir = SOLR_SEARCH_PLUGIN_DIR . '/addons';
        }
    }

    /**
     * This parses all the JSON configuration files in the addon directory and 
     * returns the addons.
     *
     * @param SolrSearch_Addon_Config $config The configuration parser. If 
     * null, this is created.
     *
     * @return array of SolrSearch_Addon_Addon
     * @author Eric Rochester <erochest@virginia.edu>
     **/
    public function parseAll($config=null)
    {
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
