<?php

// Add Domain if not present
if(!strstr($user_name, "@")) {
	$user_name = $user_name ."@". $DOMAIN_THEME;
}

