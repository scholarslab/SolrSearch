<?php

/* vim: set expandtab tabstop=2 shiftwidth=2 softtabstop=2 cc=80; */

/**
 * @package     omeka
 * @subpackage  fedora-connector
 * @copyright   2012 Rector and Board of Visitors, University of Virginia
 * @license     http://www.apache.org/licenses/LICENSE-2.0.html
 */

?>

<?php

$docs = array();
foreach ($results->response->docs as $doc) {
    $docArray = array();
    foreach ($doc as $key => $value) {
        $docArray[$key] = $value;
    }
    array_push($docs, $docArray);
}

echo json_encode(
    array(
        'docs'  => $docs,
        'start' => $results->response->start,
        'count' => $results->response->numFound
    )
);
