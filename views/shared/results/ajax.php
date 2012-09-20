<?php
/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * AJAX view
 *
 */

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

/*
 * Local variables:
 * tab-width: 4
 * c-basic-offset: 4
 * c-hanging-comment-ender-p: nil
 * End:
 */
