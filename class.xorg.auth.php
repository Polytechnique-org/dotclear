<?php

require_once dirname(__FILE__) . '/../../inc/core/class.dc.auth.php';

class xorgAuth extends dcAuth {
  private $forceSU = false;

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
      $this->user_id = $_SESSION['auth-xorg'];
    }
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
    $url .= "&url=http://murphy.m4x.org/~x2003bruneau/dotclear/auth/XorgReturn" . urlencode("?path=" . $path);
    session_write_close();
    header("Location: $url");
    exit;
  }

  private function acquireAdminRights() {
    $this->forceSU = true;
  }

  private function releaseAdminRights() {
    $this->forceSU = false;
  }

  private function createUser() {
    global $core;
    $this->acquireAdminRights();
    if (!$core->userExists($_SESSION['auth-xorg'])) {
      $cur = new cursor($this->con, 'dc_user');
      $cur->user_id = $_SESSION['auth-xorg'];
      $cur->user_pwd = md5(rand());
      $cur->user_lang = 'fr';
      $cur->user_name = $_SESSION['auth-xorg-nom'];
      $cur->user_firstname = $_SESSION['auth-xorg-prenom'];
      $cur->user_email = $_SESSION['auth-xorg'] . '@polytechnique.org';
      $cur->user_options = $core->userDefaults();
      $cur->user_default_blog = 'default'; // FIXME
      $core->addUser($cur);
      $core->setUserBlogPermissions($_SESSION['auth-xorg'], 'default', array('usage' => true,
                                                                             'contentadmin' => true,
                                                                             'admin' => true));
    }
    $this->releaseAdminRights();
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
      $_SESSION['sess_user_id'] = $_SESSION['auth-xorg'] = $_GET['forlife'];
		  $_SESSION['sess_browser_uid'] = http::browserUID(DC_MASTER_KEY);
      $_SESSION['sess_blog_id'] = 'default';
      $this->createUser();
      header("Location: http://murphy.m4x.org" . $_GET['path']);
      exit;
    }
    unset($_SESSION['auth-xorg']);
    unset($_SESSION['sess_user_id']);
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

  public function checkUser($user_id, $pwd = null, $user_key = null) {
    if (!$this->callXorg() || $user_id != $this->user_id) {
      return false;
    }
    return parent::checkUser($this->user_id);
  }

  public function check($permissions, $blog_id) {
    $this->buildFromSession();
    return parent::check($permissions, $blog_id);
  }

  public function allowPassChange() {
    return false;
  }

  public function userID() {
    $this->buildFromSession();
    return parent::userID();
  }

  public function getPermissions() {
    $this->buildFromSession();
    return parent::getPermissions();
  }

  public function getInfo($n) {
    $this->buildFromSession();
    return parent::getInfo($n);
  }

  public function getOption($n) {
    $this->buildFromSession();
    return parent::getOption($n);
  }

  public function isSuperAdmin() {
    return $this->forceSU || ($this->user_id == 'florent.bruneau.2003');
  }

  public function getOptions() {
    $this->buildFromSession();
    return parent::getOptions();
  }
}

?>
