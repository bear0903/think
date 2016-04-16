<?php
if (!defined ('DOCROOT'))die( 'Attack Error.');
header('Location: '.DOCROOT.'/mgr/redirect.php?'.$_SERVER['QUERY_STRING']);
exit;