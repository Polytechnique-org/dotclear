<?php
$core->addBehavior('initWidgets', array('xorgAuthWidgetBehavior', 'initWidget'));

class xorgAuthWidgetBehavior {
  public static function initWidget(&$w) {
    $w->create('XorgAuth', __('Auth. X.org'), array('xorgAuthWidget','widget'));
  }
}

?>
