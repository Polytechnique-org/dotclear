<?php

if (!defined('DC_BLOG_ID')) {
  define('DC_BLOG_ID', $_SERVER['DC_BLOG_ID']);
}

class xorgAuth extends dcAuth {
  public $xorg_infos = array('forlife' => null,
                             'prenom' => null,
                             'nom' => null,
                             'grpauth' => null,
                             'perms' => null);
  static public function behavior_coreBlogConstruct(&$blog) {
    global $core;
    $core->auth->sudo(array($core->auth, 'updateUserPerms'), $blog);
  }

  public function __construct(&$core) {
    parent::__construct($core);
    $core->addBehavior('coreBlogConstruct', array('xorgAuth', 'behavior_coreBlogConstruct'));
  }

  public function buildFromSession() {
    global $core;
    @header('Last-Modified:');
    if (!isset($core) || !isset($core->session)) {
      return;
    }
    if (!session_id()) {
      $core->session->start();
    }
    $_SESSION['sess_blog_id'] = $_SERVER['DC_BLOG_ID'];
    $user = @$_SESSION['auth-xorg'];
    if ($user && is_null($this->xorg_infos['forlife'])) {
      foreach ($this->xorg_infos as $key => $val) {
        $this->xorg_infos[$key] = $_SESSION['auth-xorg-' . $key];
      }
      $this->user_id = $user;
      $this->user_admin = ($_SESSION['auth-xorg-perms'] == 'admin');
      parent::checkUser($this->user_id);
      $core->getUserBlogs();
    }
  }

  public function createUser() {
    global $core;
    if (!$core->userExists($_SESSION['auth-xorg'])) {
      $cur = new cursor($this->con, 'dc_user');
      $cur->user_id = $_SESSION['auth-xorg'];
      $cur->user_pwd = md5(rand());
      $cur->user_super = ($_SESSION['auth-xorg-perms'] == 'admin');
      $cur->user_lang = 'fr';
      $cur->user_name = $_SESSION['auth-xorg-nom'];
      $cur->user_firstname = $_SESSION['auth-xorg-prenom'];
      $cur->user_displayname = $cur->user_firstname . ' ' . $cur->user_name;
      $cur->user_email = $_SESSION['auth-xorg'] . '@polytechnique.org';
      $cur->user_options = $core->userDefaults();
      $cur->user_options['post_xorg_perms'] = 'public';
      $cur->user_default_blog = $_SERVER['DC_BLOG_ID'];
      $core->addUser($cur);
    }
  }

  public function updateUserPerms(&$blog) {
    global $core;
    $this->buildFromSession();
    if (!isset($_SESSION['auth-xorg'])) {
      return;
    }
    $type = $blog->settings->get('xorg_blog_type');
    $owner = $blog->settings->get('xorg_blog_owner');
    $level = $this->xorg_infos['grpauth'];
    $rec = $core->getUser($this->userID());
    $wasAdmin = $rec->f('user_super');
    $isAdmin = $this->xorg_infos['perms'] == 'admin';
    if (($wasAdmin && !$isAdmin) || (!$wasAdmin && $isAdmin)) {
      $cur = new cursor($this->con, 'dc_user');
      $cur->user_super = $isAdmin ? '1' : '0';
      $core->updUser($this->userID(), $cur);
    }
    if ($_SESSION['xorg-group'] != $owner) {
      $this->killSession();
      return;
    }
    if (($type == 'group-admin' || $type == 'group-member') && $level == 'admin') {
      $perms = array('usage' => true,
                     'contentadmin' => true,
                     'admin' => true);
    } else if ($type == 'group-member' && $level == 'membre') {
      $perms = array('usage' => true);
    } else if ($type == 'user' && $owner == $this->xorg_infos['forlife']) {
      $perms = array('usage' => true,
                     'contentadmin' => true,
                     'admin' => true);
    } else if ($type != 'user') {
      $perms = array();
    } else {
        echo "bad session";
      return;
    }
    $core->setUserBlogPermissions($_SESSION['auth-xorg'],
                                  $blog->id,
                                  $perms);
  }


  /** Xorg SSO API */

  public function callXorg($path = null) {
    $this->buildFromSession();
    if (@$_SESSION['auth-xorg']) {
      return true;
    }
    global $core;
    if (!session_id()) {
      $core->session->start();
    }
    if (is_null($path)) {
      $path = @$_SERVER['PATH_INFO'];
    }
    $_SESSION["auth-x-challenge"] = md5(uniqid(rand(), 1));
    $_SESSION['xorg-group'] = $core->blog->settings->get('xorg_blog_owner');
    $url = "https://www.polytechnique.org/auth-groupex/utf8";
    $url .= "?session=" . session_id();
    $url .= "&challenge=" . $_SESSION["auth-x-challenge"];
    $url .= "&pass=" . md5($_SESSION["auth-x-challenge"] . XORG_AUTH_KEY);
    $type = $core->blog->settings->get('xorg_blog_type');
    if ($type == 'group-member' || $type == 'group-admin') {
      $url .= '&group=' . $core->blog->settings->get('xorg_blog_owner');
    }
    $url .= "&url=" . urlencode($core->blog->url . "auth/XorgReturn?path=" . $path);
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
    $_COOKIE[DC_SESSION_NAME] = $_GET['PHPSESSID'];
    unset($_GET['PHPSESSID']);
    if (!session_id()) {
      $core->session->start();
    }
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
      $this->sudo(array($this, 'createUser'));
      $path = $_GET['path'];
      header('Location: ' . $core->blog->url . $_GET['path']);
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
    if (!session_id()) {
      $core->session->start();
    }
    $core->session->destroy();
    if (!isset($core->blog)) {
      $blog = $core->getBlog(DC_BLOG_ID);
    } else {
      $blog = $core->blog;
    }
    $url = @$blog->url;
    if (!$url) {
      $url = $blog->f('blog_url');
    }

    header('Location: ' . $url);
    exit;
  }


  /** Dotclear dcAuth API */

  public function checkUser($user_id, $pwd = null, $user_key = null) {
    return $this->callXorg();
  }

  public function check($permissions, $blog_id) {
    $this->buildFromSession();
    return parent::check($permissions, $blog_id);
  }

  public function checkPassword($pwd) {
    $this->buildFromSession();
    return !empty($this->user_id);
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
    if ($n == 'xorg_group_member') {
      global $core;
      if ($core->blog->settings('xorg_blog_owner') != $_SESSION['xorg-group']) {
        return false;
      }
      $perm = $this->xorg_infos['grpauth'];
      return $this->isSuperAdmin() || $perm == 'admin' || $perm == 'membre';
    }
    return parent::getInfo($n);
  }

  public function getOption($n) {
    $this->buildFromSession();
    return parent::getOption($n);
  }

  public function getOptions() {
    $this->buildFromSession();
    return parent::getOptions();
  }

  public function authForm() {
    global $core;
    if (!isset($core->blog)) {
      $blog = @$core->getBlog(DC_BLOG_ID);
    } else {
      $blog = $core->blog;
    }
    $path = @$blog->url;
    if (!$path) {
      $path = $blog->f('blog_url');
    }

    return '<fieldset>'.
      '<p><a href="' . $path . 'auth/Xorg?path=/admin/index.php">Via Polytechnique.org</a></p>' .
      '</fieldset>'.
      '<p>'.__('You must accept cookies in order to use the private area.').'</p>';
  }
}

?>
