<?php

require_once dirname(__FILE__) . '/../../inc/core/class.dc.auth.php';

class xorgAuth extends dcAuth {
  public $xorg_infos = array('forlife' => null,
                             'prenom' => null,
                             'nom' => null);

  public function __construct() {
    @session_start();
    if (@$_SESSION['auth-xorg']) {
      foreach ($this->xorg_infos as $key => $val) {
        $this->xorg_infos[$key] = $_SESSION['auth-xorg-' . $key];
      }
    }
  }

  public function checkUser($user_id, $pwd = null, $user_key = null) {
//    echo "checking auth for " . $user_id;
    return parent::checkUser($user_id, $pwd, $user_key);
  }

  public function check($permissions, $blog_id) {
//    echo "Checking right to view $permissions on $blog_id";
    return parent::check($permissions, $blog_id);
  }

  public function callXorg() {
    if (@$_SESSION['auth-xorg']) {
      header("Location: http://murphy.m4x.org" . $_GET['path']);
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

  public function returnXorg() {
    if (!isset($_GET['auth'])) {
      return false;
    }
    $params = '';
    foreach($this->xorg_infos as $key => $val) {
      if(!isset($_GET[$key])) {
        return false;
      }
      $_SESSION['auth-xorg-' . $key] = $_GET[$key];
      $this->xorg_infos[$key] = $_GET[$key];
      $params .= $_GET[$key];
    }
    if (md5('1' . $_SESSION['auth-x-challenge'] . XORG_AUTH_KEY . $params . '1') == $_GET['auth']) {
      unset($_GET['auth']);
      $_SESSION['auth-xorg'] = $_GET['forlife'];
      header("Location: http://murphy.m4x.org" . $_GET['path']);
      return true;
    }
    $_SESSION['auth-xorg'] = null;
    unset($_GET['auth']);
    return false;
  }

  public function killSession() {
    @session_destroy();
    header('Location: http://murphy.m4x.org/~x2003bruneau/dotclear/');
    exit;
  }
}

?>
