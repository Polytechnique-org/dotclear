<?php

require_once dirname(__FILE__) . '/../../inc/core/class.dc.auth.php';

class xorgAuth extends dcAuth {
  public function checkUser($user_id, $pwd = null, $user_key = null) {
    echo 1;
//    echo "checking auth for " . $user_id;
    return parent::checkUser($user_id, $pwd, $user_key);
  }

  public function check($permissions, $blog_id) {
    echo "Checking right to view $permissions on $blog_id";
    return parent::check($permissions, $blog_id);
  }
}

?>
