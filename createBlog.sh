#!/bin/sh -x

# Usage:
# ./createBlog.sh type owner baseurl
# type = user | group-member | group-admin
#     * user: this is a blog for a user
#     * group-member: this is a blog for a group, all the members of the group can post
#     * group-admin: this is a blog for a group, only group admins can post
# owner = name of the owner
#     * user blog: forlife of the owner of the blog
#     * group blog: 'diminutif' of the group (from X.net database)
# url = full url of the blog (e.g http://group.blog-x.org/).

# WARNING: The script generates a .htaccess. The rewrite base might be edited to match
#         the installation. Default value is based on a blog-farm of the form
#         http://$owner.base.url/

# Once the blog has been installed and the .htaccess set-up, you can go on the administration
# page of the blog at baseurl/admin/
#
# Their, go to the section 'Widget' and add (by drag-n-drop) the widgets 'Auth X.org' and 'Copyright'.
# (should be set up by default in near future).

type=$1
owner=$2
url=$3
apache_group=www-data
rootpath=/home/web/blogs
templatepath=dotclear
baseurl=/
serviceurl="http://blog.polytechnique.org/xorgservice/createBlog"

die() {
  echo $1
  exit 1
}

( wget "$serviceurl?owner=$owner&type=$type&url=$3" -O - 2> /dev/null | grep 'blog created' ) || die "Blog creation failed"

( cd $rootpath && mkdir $owner ) || die "Can't create the repository for the blog"

cd $owner
for i in admin db inc index.php locales plugins themes; do
  ln -s $rootpath/$templatepath/$i || die "Can't add path to $i"
done
mkdir -p "$rootpath/$templatepath/public/$owner"
chgrp -R "$apache_group" "$rootpath/$templatepath/public/$owner"
chmod g+wr "$rootpath/$templatepath/public/$owner"
ln -s $rootpath/$templatepath/public/$owner public

( cat <<EOF
RewriteEngine On
RewriteBase $baseurl
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
RewriteRule (.*) index.php/\$1
RewriteRule ^index.php\$  index.php/
SetEnv DC_BLOG_ID $owner
EOF
) > .htaccess
