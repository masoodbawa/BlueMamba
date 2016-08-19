<?php

include_once(DOCUMENT_ROOT . "/inc/BlueMamba/icl.php");
include_once(DOCUMENT_ROOT . "/inc/BlueMamba/cache.php");

$conn = iil_Connect($host, $user_name, $password, $AUTH_MODE);
if($conn)
{
	// Prepends newfolder path with rootdir as necessary
	$newfolders = array
	(
		'0' => "Sent",
		'2' => "Drafts",
		'3' => "Spam",
		'4' => "Trash"
	);

	// Create Default Folders
	while(list($i, $newfolder) = each($newfolders))
	{
		if(iil_C_CreateFolder($conn, $newfolder))
		{
			iil_C_Subscribe($conn, $newfolder);
		}
	}

	iil_Close($conn);
}

?>