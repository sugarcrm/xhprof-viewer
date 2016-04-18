#!/bin/bash

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
if [ -a "$package_name.tgz" ]; then
    rm $package_name.tgz
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
 bower_components/lexer/lexer.js \
 \
 dist/$package_name

cd dist/

sed -i.bak "s/VIEWER_VERSION/$npm_package_version/g" \
 $package_name/config.php
rm $package_name/config.php.bak

tar -czf "../$package_name.tgz" $package_name

# building rpm package
mkdir -p rpmbuild/{BUILD,RPMS,SOURCES,SPECS,SRPMS,tmp}
cd rpmbuild
cp ../../$package_name.tgz SOURCES/

cat <<EOF > SPECS/$package_name.spec
# Spec file for $package_name

%define _topdir `pwd`
%define name $npm_package_name
%define version $npm_package_version
%define release 1
%define buildroot %{_topdir}/%{name}-%{version}-root

Summary: SugarCRM XHProf Viewer
Name: %{name}
Version: %{version}
Release: %{release}
License: SugarCRM Licence
Group: Development/Tools
Source: %{name}-%{version}.tgz
URL: https://github.com/sugarcrm/xhprof-viewer
BuildRoot: %{buildroot}
BuildArch: noarch

%description
%{summary}

%prep
%setup -q

%build
# Nothing to build

%install
mkdir -p \$RPM_BUILD_ROOT/var/www/html/xhp
cp -R ./* \$RPM_BUILD_ROOT/var/www/html/xhp

%files
/var/www/html/xhp

EOF

rpmbuild -bb --target=noarch --target=noarch-unknown-linux SPECS/$package_name.spec
mv RPMS/noarch/$package_name-1.noarch.rpm ../../

cd ../..
rm -r dist
ls $package_name.tgz
ls $package_name-1.noarch.rpm
