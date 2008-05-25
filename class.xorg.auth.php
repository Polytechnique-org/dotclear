<?php

require_once dirname(__FILE__) . '/../../inc/core/class.dc.auth.php';

class xorgAuth extends dcAuth {
  public $xorg_infos = array('forlife' => null,
                             'prenom' => null,
                             'nom' => null);

  public function __construct(&$core) {
    parent::__construct($core);
  }

  private function buildFromSession() {
    global $core;
    if (!isset($core) || !isset($core->session)) {
      return;
    }
    $core->session->start();
    if (@$_SESSION['auth-xorg'] && is_null($this->xorg_infos['forlife'])) {
      foreach ($this->xorg_infos as $key => $val) {
        $this->xorg_infos[$key] = $_SESSION['auth-xorg-' . $key];
      }
    }
  }

  public function checkUser($user_id, $pwd = null, $user_key = null) {
    return $this->callXorg();
//    echo "checking auth for " . $user_id;
    return parent::checkUser($user_id, $pwd, $user_key);
  }

  public function check($permissions, $blog_id) {
     $this->buildFromSession();
//    echo "Checking right to view $permissions on $blog_id";
    return parent::check($permissions, $blog_id);
  }

  public function callXorg($path = null) {
    if (is_null($path)) {
      $path = $_SERVER['REQUEST_URI'];
    }
    $this->buildFromSession();
    if (@$_SESSION['auth-xorg']) {
      return true;
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

  public function returnXorg() {
    if (!isset($_GET['auth'])) {
      return false;
    }
    $params = '';
    global $core;
    $core->session->start();
    foreach($this->xorg_infos as $key => $val) {
      if(!isset($_GET[$key])) {
        return false;
      }
      $_SESSION['auth-xorg-' . $key] = $_GET[$key];
      $params .= $_GET[$key];
    }
    if (md5('1' . $_SESSION['auth-x-challenge'] . XORG_AUTH_KEY . $params . '1') == $_GET['auth']) {
      unset($_GET['auth']);
      $_SESSION['auth-xorg'] = $_GET['forlife'];
      header("Location: http://murphy.m4x.org" . $_GET['path']);
      exit;
    }
    $_SESSION['auth-xorg'] = null;
    unset($_GET['auth']);
    echo "Failed !!!";
    return false;
  }

  public function killSession() {
    global $core;
    $core->session->start();
    $core->session->destroy();
    header('Location: http://murphy.m4x.org/~x2003bruneau/dotclear/');
    exit;
  }
}

?>
