<?php

class xorgBlogOwnerWidget {
  public static function behavior_adminBlogPreferencesForm(&$core) {
    if ($core->auth->isSuperAdmin()) {
      if (isset($_GET['id'])) {
        $settings = new dcSettings($core, $_GET['id']);
      } else {
        $settings =& $core->blog->settings;
      }
      $types = array('user' => array('text' => 'Blog d\'utilisateur',
                                     'selected' => false),
                     'group-member' => array('text' => 'Blog de groupe, édition par les membres',
                                      'selected' => false),
                     'group-admin' => array('text' => 'Blog de groupe, édition par les administrateurs',
                                            'selected' => false));
      $type = $settings->xorgauth->get('xorg_blog_type');
      if (!$type) {
        $type = 'user';
      }
      $types[$type]['selected'] = true;
      ?>
      <fieldset>
        <legend>Authentification X.org</legend>
        <div class="two-cols">
          <div class="col">
            <p>
              <label>
                Type de blog&nbsp;:
                <select name="xorg_blog_type">
      <?php
      foreach ($types as $key => $fields) {
        echo '<option value="' . $key . '"' . ($fields['selected'] ? ' selected="selected"' : '') . '>'
           . $fields['text'] . '</option>';
      }
      ?>
                </select>
              </label>
            </p>
          </div>
          <div class="col">
            <p>
              <label>
                Propriétaire du blog (*)&nbsp;:
                <input type="text" name="xorg_blog_owner" value="<?php echo $settings->xorgauth->get('xorg_blog_owner'); ?> " />
              </label>
            </p>
            <p>
              <label>
                (*) Dans le cas d'un blog de groupe, le propriétaire est le diminutif X.net du groupe<br />
                (*) Dans le cas d'un blog d'utilisateur, le propriétaire est le forlife de l'utilisateur
              </label>
            </p>
          </div>
        </div>
      </fieldset>
      <?php
    }
  }

  public static function behavior_adminBeforeBlogSettingsUpdate(&$settings) {
    self::setXorgOwner($settings, $_POST['xorg_blog_type'], $_POST['xorg_blog_owner']);
  }

  public static function setXorgOwner(&$settings, $type, $owner) {
    global $core;
    if ($core->auth->isSuperAdmin()) {
      $settings->xorgauth->put('xorg_blog_type', $type, 'string', 'Type de blog X.org');
      $settings->xorgauth->put('xorg_blog_owner', $owner, 'string', 'Propriétaire X.org du blog');
    }
  }

}

?>
