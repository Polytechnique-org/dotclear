<?php
require_once dirname(__FILE__) . '/widget.auth.php';

$core->addBehavior('initWidgets', array('xorgAuthWidget', 'behavior_initWidgets'));
?>
