<?php

require_once dirname(__FILE__) . '/widget.auth.php';
require_once dirname(__FILE__) . '/widget.copyright.php';
require_once dirname(__FILE__) . '/widget.post.perms.php';
require_once dirname(__FILE__) . '/widget.blog.owner.php';

$core->addBehavior('coreBlogConstruct', array('xorgAuth', 'behavior_coreBlogConstruct'));


/* Declare the authentication widget on public page */
$core->addBehavior('initWidgets', array('xorgAuthWidget', 'behavior_initWidgets'));
$core->addBehavior('initWidgets', array('xorgCopyrightWidget', 'behavior_initWidgets'));


/* Declare stuff to set permissions on each post */
$core->addBehavior('adminPostFormSidebar', array('xorgPostPermsWidget', 'behavior_adminPostFormSidebar'));
$core->addBehavior('adminBeforePostCreate', array('xorgPostPermsWidget', 'behavior_adminBeforePostCreate'));
$core->addBehavior('adminBeforePostUpdate', array('xorgPostPermsWidget', 'behavior_adminBeforePostUpdate'));

/* Stuff to set user preferences about post permissions */
$core->addBehavior('adminPreferencesForm', array('xorgPostPermsWidget', 'behavior_adminPreferencesForm'));
$core->addBehavior('adminBeforeUserUpdate', array('xorgPostPermsWidget', 'behavior_adminBeforeUserUpdate'));


/* Declare the form to assign the ownership of the blog */
$core->addBehavior('adminBlogPreferencesForm', array('xorgBlogOwnerWidget', 'behavior_adminBlogPreferencesForm'));
$core->addBehavior('adminBeforeBlogSettingsUpdate', array('xorgBlogOwnerWidget', 'behavior_adminBeforeBlogSettingsUpdate'));
?>
