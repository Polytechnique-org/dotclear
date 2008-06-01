<?php

require_once dirname(__FILE__) . '/page.auth.php';
require_once dirname(__FILE__) . '/widget.auth.php';
require_once dirname(__FILE__) . '/widget.post.perms.php';
require_once dirname(__FILE__) . '/class.xorg.auth.php';
require_once dirname(__FILE__) . '/page.webservice.php';

/* Xorg auth */
$core->url->register('xorgAuth', 'XorgAuth', '^auth/(.*)$', array('xorgAuthentifier', 'doAuth'));

/* Post permission handling */
$core->addBehavior('coreBlogGetPosts', array('xorgPostPermsWidget', 'behavior_coreBlogGetPosts'));

/* Webservice to create new blog */
$core->url->register('xorgWebservice', 'XorgWebservice', '^xorgservice/(.*)$', array('XorgWebservice', 'handle'));
?>
