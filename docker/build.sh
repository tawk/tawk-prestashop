#!/bin/sh
set -e;

img_env_fn="${1##*/}";
ps_dir="prestashop1.7";
if { ! [ -z $1 ] && { [ $img_env_fn = "prestashop-16-php-56.env" ] || [ $img_env_fn = "prestashop-16-php-72.env" ]; } } then
	ps_dir="prestashop1.6";
fi

build_dir=$(dirname $0);
module_dir=$build_dir/bin/tawkto;

if [ -d "$module_dir" ]; then
	echo "Removing existing module folder";
	rm -r $module_dir;
fi

echo "Creating module folder";
mkdir -p $module_dir;

echo "Installing dependency"
composer run build --working-dir=$build_dir/..

echo "Copying files to module folder";
cp -r $build_dir/../$ps_dir/* $module_dir

echo "Done building module folder";

echo "Building docker image"
if [ -z $1 ]; then
	docker-compose build;
else
	docker-compose --env-file $1 build;
fi
