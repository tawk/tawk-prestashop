#!/bin/sh

build_dir=$(dirname $0);

build_release_file() {
    if [ -z "$1" ]
        then
            echo "Prestashop version wasn't specified";
            return;
    fi

    ps_version=$1;
    ps_dir=$build_dir/../prestashop$ps_version/;

    echo "Building Prestashop $ps_version..."
    echo "Creating temporary directory"
    rm -rf $build_dir/tawkto
    mkdir $build_dir/tawkto

    echo "Copying files to temporary directory"
    cp -r $ps_dir/* $build_dir/tawkto/

    echo "Retrieving release version"
    release_version=$(retrieve_version $ps_dir);

    echo "Creating zip file"
    (cd $build_dir && zip -9 -rq tawk-prestashop-$ps_version-$release_version.zip tawkto)

    echo "Cleaning up"
    rm -rf $build_dir/tawkto

    echo "Done building Prestashop $ps_version!"
}

retrieve_version() {
    ps_dir=$1;
    awk 'gsub(/<version><!\[CDATA\[|]]><\/version>/,"")' $ps_dir/config.xml | xargs;
}

build_release_file 1.6;
build_release_file 1.7;
