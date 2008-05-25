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
    switch ($args) {
     case 'exit':
      self::killSession();
      break;
     case 'Xorg':
      self::callXorg();
      break;
     case 'XorgReturn':
      self::returnXorg();
      break;
     default:
      self::p404();
    }
    return;
  }

  static protected function callXorg() {
    if (@$_SESSION['auth-xorg']) {
      header("Location: http://murphy.m4x.org/" . $_GET['path']);
      return;
    }
    $_SESSION["auth-x-challenge"] = md5(uniqid(rand(), 1));
    $url = "https://www.polytechnique.org/auth-groupex/utf8";
    $url .= "?session=" . session_id();
    $url .= "&challenge=" . $_SESSION["auth-x-challenge"];
    $url .= "&pass=" . md5($_SESSION["auth-x-challenge"] . XORG_AUTH_KEY);
    $url .= "&url=http://murphy.m4x.org/~x2003bruneau/dotclear/auth/XorgReturn" . urlencode("?path=" . $_GET['path']);
    session_write_close();
    header("Location: $url");
    exit;
  }

  static protected function returnXorg() {
    if (!isset($_GET['auth'])) {
      return false;
    }
    global $core;
    $params = '';
    foreach($core->auth->xorg_infos as $key => $val) {
      if(!isset($_GET[$key])) {
        return false;
      }
      $_SESSION['auth-xorg-' . $key] = $_GET[$key];
      $core->auth->xorg_infos[$key] = $_GET[$key];
      $params .= $_GET[$key];
    }
    if (md5('1' . $_SESSION['auth-x-challenge'] . XORG_AUTH_KEY . $params . '1') == $_GET['auth']) {
      unset($_GET['auth']);
      $_SESSION['auth-xorg'] = $_GET['forlife'];
      header("Location: http://murphy.m4x.org/" . $_GET['path']);
      return true;
    }
    $_SESSION['auth-xorg'] = null;
    unset($_GET['auth']);
    return false;
  }

  static protected function killSession() {
    @session_destroy();
    header('Location: http://murphy.m4x.org/~x2003bruneau/dotclear/');
    exit;
  }
}
?>
