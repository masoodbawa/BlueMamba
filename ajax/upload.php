<?php
/**
 * Upload File
 */

include_once('../inc/config.php');

$document = new Document();
$result = $document->upload($member->info->id, 'Filedata');
print_r(json_encode($result));

