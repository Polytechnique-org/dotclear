<?php

class xorgAuthentifier extends dcUrlHandlers {
  static public function doAuth($args) {
    global $core;
    switch ($args) {
     case 'exit':
      $core->auth->killSession();
      break;
     case 'Xorg':
      if ($core->auth->callXorg($_GET['path'])) {
        header('Location: ' . $core->blog->url . $_GET['path']);
        exit;
      }
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
