<?php
class xorgAuthWidget {
  public static function behavior_initWidgets(&$w) {
    $w->create('XorgAuth', __('Auth. X.org'), array('xorgAuthWidget', 'widget'));
  }

  static public function widget(&$w) {
    global $core;
    $name = $core->auth->userID();
    var_dump($_SESSION);
    var_dump($_REQUEST);
    echo "sessionid = " . session_id();
    if ($name) {
      return '<p>Tu es ' . $core->auth->getInfo('user_displayname') . '<br />'
           . '<a href="' . $core->blog->url . 'auth/exit">déconnexion</a></p>';
    } else {
      return '<p><a href="' . $core->blog->url . 'auth/Xorg?path=' . $_SERVER['REQUEST_URI'] . '">M\'authentifier via Polytechnique.org</a></p>';
    }
  }
}

?>
