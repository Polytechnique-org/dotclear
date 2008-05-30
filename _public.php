<?php
require_once dirname(__FILE__) . '/page.auth.php';
require_once dirname(__FILE__) . '/widget.auth.php';
require_once dirname(__FILE__) . '/widget.post.perms.php';

$core->url->register('xorgAuth', 'Xorg', '^auth/(.*)$', array('xorgAuthentifier', 'doAuth'));

$core->addBehavior('coreBlogGetPosts', array('xorgPostPermsWidget', 'behavior_coreBlogGetPosts'));
?>
