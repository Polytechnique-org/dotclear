<?php
require_once dirname(__FILE__) . '/widget.auth.php';
require_once dirname(__FILE__) . '/widget.post.perms.php';

/* Declare the authentication widget on public page */
$core->addBehavior('initWidgets', array('xorgAuthWidget', 'behavior_initWidgets'));


/* Declare stuff to set permissions on each post */
$core->addBehavior('adminPostFormSidebar', array('xorgPostPermsWidget', 'behavior_adminPostFormSidebar'));
$core->addBehavior('adminBeforePostCreate', array('xorgPostPermsWidget', 'behavior_adminBeforePostCreate'));
$core->addBehavior('adminBeforePostUpdate', array('xorgPostPermsWidget', 'behavior_adminBeforePostUpdate'));

/* Stuff to set user preferences about post permissions */
$core->addBehavior('adminPreferencesForm', array('xorgPostPermsWidget', 'behavior_adminPreferencesForm'));
$core->addBehavior('adminBeforeUserUpdate', array('xorgPostPermsWidget', 'behavior_adminBeforeUserUpdate'));
?>
