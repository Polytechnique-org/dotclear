<?php
$core->url->register('xorgAuth', 'Xorg', '^auth/(.*)$', array('xorgAuthentifier', 'doAuth'));

class xorgAuthWidget {
  static public function widget(&$w) {
    global $core;
    if ($core->auth->xorg_infos['forlife']) {
      return '<p>Tu es ' . $core->auth->xorg_infos['prenom'] . ' ' . $core->auth->xorg_infos['nom'] . '<br />'
           . '<a href="auth/exit">déconnexion</a></p>';
    } else {
      return '<p><a href="auth/Xorg?path=' . $_SERVER['REQUEST_URI'] . '">M\'authentifier via Polytechnique.org</a></p>';
    }
  }
}

class xorgAuthentifier extends dcUrlHandlers {
  static public function doAuth($args) {
    @session_start();
    global $core;
    switch ($args) {
     case 'exit':
      $core->auth->killSession();
      break;
     case 'Xorg':
      $core->auth->callXorg();
      break;
     case 'XorgReturn':
      $core->auth->returnXorg();
      break;
     default:
      self::p404();
    }
    return;
  }

}
?>
