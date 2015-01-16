<?php

/**
 * @package     omeka
 * @subpackage  solr-search
 * @copyright   2012 Rector and Board of Visitors, University of Virginia
 * @license     http://www.apache.org/licenses/LICENSE-2.0.html
 */


/**
 * This is a static class of utility methods.
 **/
class SolrSearch_Utils
{


    /**
     * This tests whether the view is available or not.
     *
     * @return Omeka_View
     * @author Eric Rochester
     **/
    public static function ensureView()
    {
        if (! Zend_Registry::isRegistered('view')) {
            $view = new Omeka_View();
            Zend_Registry::set('view', $view);
        }
        return Zend_Registry::get('view');
    }

    /**
     * <`4:#:undocumented function`>
     *
     * @return <`5:void`>
     * @author <`6`>
     **/
    public static function nav_li($tab, $key, $url, $label)
    {
        echo "<li";
        if ($tab == $key) {
            echo " class='current'";
        }
        echo "><a href='$url'>$label</a></li>\n";
    }

}
