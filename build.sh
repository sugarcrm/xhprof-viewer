#!/usr/bin/env sh
#
# © 2016 SugarCRM Inc.  Licensed by SugarCRM under the Apache 2.0 license.
#

if [ "$npm_package_name" != "xhprof-viewer" ]; then
    echo 'Run `npm run build` instead';
    exit 1;
fi;

npm install
bower install
composer install

if [ -a 'dist' ]; then
    rm -r ./dist
fi;

package_name=$npm_package_name-$npm_package_version
archive_name=$npm_package_name
if [ -a "$archive_name.tar.gz" ]; then
    rm $archive_name.tar.gz
fi;

mkdir -p dist/$package_name
rsync -Rr --exclude=.DS_Store \
 src \
 vendor \
 xhprof \
 config.php \
 templates \
 index.php \
 README.md \
 LICENSE \
 CONTRIBUTING.md \
 CONTRIBUTOR_TERMS.pdf \
 THIRD_PARTY_LICENSES.pdf \
 bower_components/jquery/dist/jquery.min.js \
 bower_components/bootstrap/dist/css/bootstrap.min.css \
 bower_components/bootstrap/dist/js/bootstrap.min.js \
 bower_components/bootstrap/dist/fonts/glyphicons-halflings-regular.woff2 \
 bower_components/bootstrap-datepicker/dist/js/bootstrap-datepicker.min.js \
 bower_components/bootstrap-datepicker/dist/css/bootstrap-datepicker.min.css \
 bower_components/font-awesome/css/font-awesome.min.css \
 bower_components/font-awesome/fonts/fontawesome-webfont.woff2 \
 bower_components/highlightjs/styles/default.css \
 bower_components/highlightjs/highlight.pack.min.js \
 bower_components/highlightjs/highlight.pack.min.js \
 bower_components/lexer/lexer.js \
 \
 dist/$package_name || (echo 'Failed to rsync' 1>&2 && exit 1);

cd dist/

sed -i.bak "s/VIEWER_VERSION/$npm_package_version/g" \
 $package_name/config.php
rm $package_name/config.php.bak

tar -czf "../$archive_name.tar.gz" $package_name
zip -qr "../$archive_name.zip" $package_name

cd ..
rm -r dist
ls $archive_name.tar.gz
ls $archive_name.zip
