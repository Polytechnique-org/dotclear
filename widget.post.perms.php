<?php

class xorgPostPermsWidget {
  public static function behavior_adminPostFormSidebar($post) {
    $choices = array('public' => array('text' => 'Visible par tous',
                                       'selected' => false),
                     'auth'   => array('text' => 'Visible par les X',
                                       'selected' => false));
    $pos = 'public';
    if (!is_null($post)) {
      $metas = unserialize($post->field('post_meta'));
      if (isset($metas['post_xorg_perms'])) {
        $pos = $metas['post_xorg_perms'];
      }
    } else {
      global $core;
      $pos = $core->auth->getOption('post_xorg_perms');
      if ($pos && !isset($choices[$pos])) {
        $pos = 'auth';
      }
    }
    $choices[$pos]['selected'] = true;
    echo '<p><label>Visibilité du billet&nbsp;:'
         . '  <select name="post_xorg_perms">';
    foreach ($choices as $val => $fields) {
      echo '<option value="' . $val . '"' . ($fields['selected'] ? ' selected="selected"' : '') . '>'
         . $fields['text'] . '</option>';
    }
    echo '  </select>'
       . '</label></p>';
  }

  private static function setPermsMeta(&$cur) {
    $meta = $cur->getField('post_meta');
    if (is_string($meta)) {
      $meta = unserialize($meta);
    }
    if (!is_array($meta)) {
      $meta = array();
    }
    $meta['post_xorg_perms'] = $_POST['post_xorg_perms'];
    $cur->setField('post_meta', serialize($meta));
  }

  public static function behavior_adminBeforePostCreate(&$cur) {
    self::setPermsMeta($cur);
  }

  public static function behavior_adminBeforePostUpdate(&$cur, $post_id) {
    self::setPermsMeta($cur);
  }


  public static function behavior_adminPreferencesForm($core) {
    $levels = array('public' => array('text' => 'Visible par tous',
                                      'selected' => false),
                    'auth' => array('text' => 'Visible par les X',
                                    'selected' => false),
                    'group' => array('text' => 'Visible par les membres du groupe (1)',
                                     'selected' => false));
    $pos = $core->auth->getOption('post_xorg_perms');
    if (!$pos || !isset($levels[$pos])) {
      $pos = 'public';
    }
    $levels[$pos]['selected'] = true;
    echo '<p><label>Visibilité nouveaux billets par défaut&nbsp;:'
       . '  <select name="post_xorg_perms">';
    foreach ($levels as $key => $fields) {
      echo '<option value="' . $key . '"' . ($fields['selected'] ? ' selected="selected"' : '') . '>'
         . $fields['text'] . '</option>';
    }
    echo '</select>'
       . '(1) Ne concerne que les blogs de groupes X. Equivaut à "Visible par les X" sur les autres blogs"'
       . '</label></p>';
  }

  public static function behavior_adminBeforeUserUpdate(&$cur, $user_id) {
    $opts = $cur->getField('user_options');
    $opts['post_xorg_perms'] = $_POST['post_xorg_perms'];
    $cur->setField('user_options', $opts);
  }

  public static function behavior_coreBlogGetPosts(&$rs) {
    $rs->extend('xorgPostPermsFilter');
  }

/*  public static function behavior_coreBlogGetComments(&$rs) {
    $rs->extends('xorgCommentPermsFilter');
  }*/
}

if (class_exists('rsExtPostPublic')) {

class xorgPostPermsFilter extends rsExtPostPublic {
  private static function canRead(&$rs) {
    $metas = unserialize($rs->field('post_meta'));
    global $core;
    if (!isset($metas['post_xorg_perms'])) {
      return true;
    } elseif ($metas['post_xorg_perms'] == 'public') {
      return true;
    } elseif ($metas['post_xorg_perms'] == 'auth' && $core->auth->userID()) {
      return true;
    } elseif ($metas['post_xorg_perms'] == 'group' && $core->auth->getInfo('xorg_group_member')) {
      return true;
    }
    return false;
  }

  public static function getContent(&$rs, $absolute_urls = false) {
    if (self::canRead($rs)) {
      return parent::getContent(&$rs, $absolute_urls);
    } else if (!self::isExtended($rs)) {
      return '<p class="error">Vous n\'avez pas les droits suffisant pour lire ce billet</p>';
    } else {
      return null;
    }
  }

  public static function getExcerpt(&$rs, $absolute_urls = false) {
    if (self::canRead($rs)) {
      return parent::getContent(&$rs, $absolute_urls);
    } else if (self::isExtended($rs)) {
      return 'Vous n\'avez pas les droits suffisant pour lire ce billet';
    } else {
      return null;
    }
  }

  public static function commentsActive(&$rs) {
    return self::canRead($rs) && parent::commentsActive($rs);
  }

  public static function trackbacksActive(&$rs) {
    return self::canRead($rs) && parent::trackbacksActive($rs);
  }

  public static function hasComments(&$rs) {
    return self::canRead($rs) && parent::hasComments($rs);
  }
}
}
?>
