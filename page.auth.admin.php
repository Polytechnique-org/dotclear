<?php

class xorgLoginPage extends dcUrlHandlers {
  static public function page($args) {
    switch ($args) {
      case 'xorg.php':
        self::dispatchForm();
      default:
        self::p404();
    }
  }

  static protected function dispatchForm() {
    # If we have a session cookie, go to index.php
    if (isset($_SESSION['sess_user_id']))
    {
      global $core;
      header('Location: ' . $core->blog->url . 'admin/index.php');
    }

    # Loading locales for detected language
    $dlang = http::getAcceptLanguage();
    if ($dlang) {
      l10n::set(dirname(__FILE__).'/../locales/'.$dlang.'/main');
    }

    global $core;
    $msg = $err = null;
    header('Content-Type: text/html; charset=UTF-8');
    ?>
    <!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"  "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
    <html xmlns="http://www.w3.org/1999/xhtml"
    xml:lang="en" lang="en">
    <head>
      <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
      <meta http-equiv="Content-Script-Type" content="text/javascript" />
      <meta http-equiv="Content-Style-Type" content="text/css" />
      <meta http-equiv="Content-Language" content="en" />
      <meta name="MSSmartTagsPreventParsing" content="TRUE" />
      <meta name="ROBOTS" content="NOARCHIVE,NOINDEX,NOFOLLOW" />
      <meta name="GOOGLEBOT" content="NOSNIPPET" />
      <title><?php echo html::escapeHTML(DC_VENDOR_NAME); ?></title>

    <?php
    echo dcPage::jsLoadIE7();
    echo dcPage::jsCommon();
    ?>

      <style type="text/css">
      @import url(style/default.css); 
      </style>
      <?php
      # --BEHAVIOR-- loginPageHTMLHead
      $core->callBehavior('loginPageHTMLHead');
      ?>
    </head>

    <body id="dotclear-admin" class="auth">

    <form action="xorg.php" method="post" id="login-screen">
    <h1><?php echo html::escapeHTML(DC_VENDOR_NAME); ?></h1>

    <?php
    if ($err) {
      echo '<div class="error">'.$err.'</div>';
    }
    if ($msg) {
      echo '<p class="message">'.$msg.'</p>';
    }

    {
      echo
      '<fieldset>'.
      '<p><a href="' . $core->blog->url . 'auth/Xorg?path=/~x2003bruneau/dotclear/admin/index.php">Via Polytechnique.org</a></p>' .
      '<p><a href="' . $core->blog->url . 'admin/auth.php">Via le formulaire</a></p>' .
      '</fieldset>'.
      
      '<p>'.__('You must accept cookies in order to use the private area.').'</p>';
      
      if ($core->auth->allowPassChange()) {
        echo '<p><a href="auth.php?recover=1">'.__('I forgot my password').'</a></p>';
      }
    }
    ?>
    </form>

    <script type="text/javascript">
    //<![CDATA[
    $('input[@name="user_id"]').get(0).focus();
    //]]>
    </script>

    </body>
    </html>
<?php
    exit;
  }
}

?>
