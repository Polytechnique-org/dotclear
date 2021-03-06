<?php
require_once dirname(__FILE__) . '/widget.blog.owner.php';

class XorgWebservice extends dcUrlHandlers {
  static private function canRunServices() {
    $addrs = explode(',', XORG_SERV_ADDRS);
    foreach ($addrs as $addr) {
      if ($addr == $_SERVER['REMOTE_ADDR']) {
        return true;
      }
    }
    return false;
  }

  static public function handle($args) {
    if (!self::canRunServices()) {
      header($_SERVER['SERVER_PROTOCOL'] . ' 403 Forbidden');
      echo "You're not allowed to run the webservices";
      exit;
    }
    $service = null;
    switch ($args) {
      case 'createBlog':
        $service = array('XorgWebservice', $args);
        break;
    }
    if ($service == null) {
      header($_SERVER['SERVER_PROTOCOL'] . ' 404 Not Found');
      echo 'Webservice does not handle "' . $args . '"';
      exit;
    }
    global $core;
    $result = $core->auth->sudo($service);
    if ($result['status']) {
      header($_SERVER['SERVER_PROTOCOL'] . ' 200 Success');
    } else {
      header($_SERVER['SERVER_PROTOCOL'] . ' 500 Internal Server Error');
    }
    echo $result['message'];
    exit;
  }

  static public function createBlog() {
    global $core;
    if (!isset($_GET['owner']) || !isset($_GET['url']) || !isset($_GET['type']) || !isset($_GET['baseurl'])) {
      return array('status' => false,
                   'message' => 'Missing parameters');
    }
    $owner = $_GET['owner'];
    $url   = rtrim($_GET['url'], '/') . '/';
    $baseurl = rtrim($_GET['baseurl'], '/') . '/';
    $type  = $_GET['type'];
    if ($type != 'user' && $type != 'connected' && $type != 'group-member' && $type != 'group-admin') {
      return array('status' => false,
                   'message' => 'Invalid blog type required');
    }
    if (isset($_GET['ownername'])) {
      $ownername = $_GET['ownername'];
    } else {
      $ownername = $owner;
    }

    $cur = new cursor($core->con, 'dc_blog');
    $cur->blog_id  = $owner;
    $cur->blog_uid = $owner;
    $cur->blog_url = $url;
    $cur->blog_name = 'Blog de ' . $ownername;
    $cur->blog_status = 1;
    $core->addBlog($cur);

    $settings = new dcSettings($core, $owner);
    $settings->system->put('public_path', 'public/' . $owner);
    $settings->system->put('public_url', $baseurl . 'public/');
    $settings->system->put('themes_path', 'themes/');
    $settings->system->put('themes_url', $baseurl . 'themes/');
    $xorgauth = $settings->addNamespace('xorgauth');
    $xorgauth->put('xorg_blog_type', $type, 'string', 'Type de blog X.org');
    $xorgauth->put('xorg_blog_owner', $owner, 'string', 'Propriétaire X.org du blog');

    return array('status' => true,
                 'message' => 'blog created');
  }
}

?>
