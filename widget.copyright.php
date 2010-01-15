<?php
class xorgCopyrightWidget {
  public static function behavior_initWidgets(&$w) {
    $w->create('XorgCopyright', __('Copyright'), array('xorgCopyrightWidget', 'widget'));
  }

  static public function widget(&$w) {
    global $core;
    $copyright = $core->blog->settings->system->get('copyright_notice');
    $editor    = $core->blog->settings->system->get('editor');

    $text = '<div><h2>Mentions légales</h2><ul>';
    if ($editor) {
      $text .= '<li>Editeur&nbsp;: ' . $editor . '</li>';
    }
    $text .= '<li>Hébergé par <a href="http://xorg.polytechnique.org">Polytechnique.org</a></li>';
    if ($copyright) {
      $text .= '<li>' . $copyright . '</li>';
    }
    return $text . '</ul></div>';
  }
}

?>
