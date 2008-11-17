#!/bin/sh -x

type=$1
owner=$2
url=$3
apache_group=www-data
rootpath=/home/x2003bruneau/public_html/
templatepath=dotclear
baseurl=/~x2003bruneau
serviceurl="http://murphy.m4x.org/~x2003bruneau/dotclear2/xorgservice/createBlog"

die() {
  echo $1
  exit 1
}

( wget "$serviceurl?owner=$owner&type=$type&url=$3" -O - 2> /dev/null | grep 'blog created' ) || die "Blog creation failed"

( cd $rootpath && mkdir $owner ) || die "Can't create the repository for the blog"

cd $owner
for i in admin db inc index.php locales plugins themes public ; do
  ln -s $rootpath/$templatepath/$i || die "Can't add path to $i"
done
mkdir -p "$rootpath/$templatepath/public/$owner"

( cat <<EOF
RewriteEngine On
RewriteBase $baseurl/$owner
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
RewriteRule (.*) index.php/\$1
RewriteRule ^index.php\$  index.php/
SetEnv DC_BLOG_ID $owner
EOF
) > .htaccess
