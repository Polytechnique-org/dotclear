<?php
class xorgAuthWidget {
  public static function behavior_initWidgets(&$w) {
    $w->create('XorgAuth', __('Auth. X.org'), array('xorgAuthWidget', 'widget'));
  }

  static public function widget(&$w) {
    global $core;
    $name = @$core->auth->getInfo('user_displayname');
    if ($name) {
      $str = '<p>Tu es ' . $core->auth->getInfo('user_displayname') . '<br />';
      if ($core->auth->check('usage,contentadmin,admin', $core->blog->id)) {
        $str .= '<a href="' . $core->blog->url . 'admin/index.php">Interface de rédaction</a><br />';
      }
      return $str . '<a href="' . $core->blog->url . 'auth/exit">Déconnexion</a></p>';
    } else {
      return '<p><a href="' . $core->blog->url . 'auth/Xorg?path=' . $_SERVER['PATH_INFO'] . '">M\'authentifier via Polytechnique.org</a></p>';
    }
  }
}

?>
