<?php

class xorgBlogOwnerWidget {
  public static function behavior_adminBlogPreferencesForm(&$core) {
    if ($core->auth->isSuperAdmin()) {
      $types = array('user' => array('text' => 'Blog d\'utilisateur',
                                     'selected' => false),
                     'group-member' => array('text' => 'Blog de groupe, édition par les membres',
                                      'selected' => false),
                     'group-admin' => array('text' => 'Blog de groupe, édition par les administrateurs',
                                            'selected' => false));
      $type = $core->blog->settings->get('xorg_blog_type');
      if (!$type) {
        $type = 'user';
      }
      $types[$type]['selected'] = true;
      echo '<fieldset><legend>Authentification X.org</legend><div class="two-cols"><div class="col">';
      echo '<p><label>Type de blog&nbsp;:'
         . '<select name="xorg_blog_type">';
      foreach ($types as $key => $fields) {
        echo '<option value="' . $key . '"' . ($fields['selected'] ? ' selected="selected"' : '') . '>'
           . $fields['text'] . '</option>';
      }
      echo '</select></label></p></div>';
      echo '<div class="col"><p><label>Propriétaire du blog (*)&nbsp;:<input type="text" name="xorg_blog_owner" value="' . $core->blog->settings->get('xorg_blog_owner') . '" /></label></p>';
      echo '<p><label>(*) Dans le cas d\'un blog de groupe, le propriétaire est le diminutif X.net du groupe<br />(*) Dans le cas d\'un blog d\'utilisateur, le propriétaire est le forlife de l\'utilisateur</label></p></div></div></fieldset>';
    }
  }

  public static function behavior_adminBeforeBlogSettingsUpdate(&$settings) {
    global $core;
    if ($core->auth->isSuperAdmin()) {
      $settings->put('xorg_blog_type', $_POST['xorg_blog_type'], 'string', 'Type de blog X.org');
      $settings->put('xorg_blog_owner', $_POST['xorg_blog_owner'], 'string', 'Propriétaire X.org du blog');
    }
  }
}

?>
