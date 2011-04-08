#!/bin/bash

# Usage:
# ./createBlog.sh type owner baseurl
# type = user | connected | group-member | group-admin
#     * user: this is a blog for a user
#     * connected: this is a blog for a group, all connected users can post
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


# HELPERS

usage() {
  echo "Usage: $0 -t TYPE -o OWNER -u URL

  where :
    TYPE = user | connected | group-member | group-admin is the type of the blog
    OWNER if the owner of the blog (user hruid or group diminutif)
    URL is the URL of the blog
  "
    exit $1;
}

die() {
  echo $1
  exit 1
}


# GETOPT

TEMP=`getopt -n $0 -o ht:o:u: -- "$@"`

RET=$?

if [ $RET != 0 ]; then
  usage $RET
fi

eval set -- "$TEMP"

TYPE=""
OWNER=""
URL=""
VHOST=0

while true ; do
  case "$1" in
    -h)
      usage 0
      ;;
    -t)
      TYPE=$2;
      if [[ "${TYPE}" != "user" && "${TYPE}" != "group-member" && "${TYPE}" != "group-admin" && "${TYPE}" != "connected" ]]; then
        echo -e "ERROR: TYPE must be one of: user | group-member | group-admin | connected\n";
        usage 1
      fi
      shift 2;
      ;;
    -o)
      OWNER=$2;
      shift 2;
      ;;
    -u)
      URL=$2;
      if ! echo "${URL}" | grep -E "^http://[^/]+/"  > /dev/null; then
        echo -e "ERROR: URL must be a full URL, e.g 'http://www.example.org/'\n";
        usage 1
      fi
      shift 2;
      ;;
    --)
      shift;
      break;
      ;;
    *)
      echo -e "ERROR: Invalid argument '$1'\n"
      usage 1
      ;;
  esac
done

if [[ "x${TYPE}" == "x" || "x${OWNER}" == "x" || "x${URL}" == "x" ]]; then
  echo -e "ERROR: Missing one of -t, -o or -u options.\n"
  usage 1
fi

BASEURL=`echo "${URL}" | sed -r 's,http://[^/]+/,/,'`

echo "Creating blog with :
  TYPE = ${TYPE}
  OWNER = ${OWNER}
  URL = ${URL}
  BASEURL = ${BASEURL}
"

apache_group=www-data
rootpath=/home/web/blogs
templatepath=dotclear

serviceurl="http://blog.polytechnique.org/xorgservice/createBlog"

CALLED="$serviceurl?owner=${OWNER}&type=${TYPE}&url=${URL}&baseurl=${BASEURL}"
echo "Calling $CALLED"
( wget "$CALLED" -O - 2> /dev/null | grep 'blog created' ) || die "Blog creation failed"

( cd $rootpath && mkdir -p $OWNER ) || die "Can't create the repository for the blog ($rootpath/$OWNER)"

cd $rootpath/$OWNER
for i in admin db inc index.php locales plugins themes; do
  ln -s $rootpath/$templatepath/$i || die "Can't add path to $i"
done
mkdir -p "$rootpath/$templatepath/public/$OWNER"
chgrp -R "$apache_group" "$rootpath/$templatepath/public/$OWNER"
chmod g+wr "$rootpath/$templatepath/public/$OWNER"
ln -s $rootpath/$templatepath/public/$OWNER public

( cat <<EOF
RewriteEngine On
RewriteBase ${BASEURL}
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
RewriteRule (.*) index.php/\$1
RewriteRule ^index.php\$  index.php/
SetEnv DC_BLOG_ID $OWNER
EOF
) > .htaccess
