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
}

?>
