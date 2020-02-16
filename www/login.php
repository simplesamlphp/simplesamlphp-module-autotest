<?php

use Exception;
use SimpleSAML\Auth;
use SimpleSAML\Error;

try {
    if (!isset($_GET['SourceID'])) {
        throw new Error\BadRequest('Missing SourceID parameter');
    }
    $sourceId = $_GET['SourceID'];

    $as = new Auth\Simple($sourceId);

    $as->requireAuth();

    header('Content-Type: text/plain; charset=utf-8');
    echo "OK\n";
} catch (Exception $e) {
    header('HTTP/1.0 500 Internal Server Error');
    header('Content-Type: text/plain; charset=utf-8');
    echo "ERROR\n";
    echo $e->getMessage() . "\n";
}
