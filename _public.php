<?php
require_once dirname(__FILE__) . '/page.auth.php';
require_once dirname(__FILE__) . '/page.auth.admin.php';
require_once dirname(__FILE__) . '/widget.auth.php';

$core->url->register('xorgAuth', 'Xorg', '^auth/(.*)$', array('xorgAuthentifier', 'doAuth'));
$core->url->register('xorgLogin', 'XorgLogin', '^admin/(xorg\.php)$', array('xorgLoginPage', 'page')); 



?>
