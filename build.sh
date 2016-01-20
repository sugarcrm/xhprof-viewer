#!/bin/bash

if [ "$npm_package_name" != "xhprof-viewer" ]; then
    echo 'Run `npm run build` instead';
    exit 1;
fi;

npm install
bower install

if [ -a 'dist' ]; then
    rm -r ./dist
fi;

package_name=$npm_package_name-$npm_package_version
if [ -a "$package_name.tgz" ]; then
    rm $package_name.tgz
fi;

mkdir -p dist/$package_name
rsync -Rr --exclude=.DS_Store \
 xhprof \
 config.php \
 CustomViewXhProf.php \
 index.php \
 README.md \
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
 bower_components/algolia-autocomplete.js/dist/autocomplete.jquery.min.js \
 \
 dist/$package_name

cd dist/
tar -czf "../$package_name.tgz" $package_name
cd ..
rm -r dist
ls $package_name.tgz
