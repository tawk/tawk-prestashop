#!/bin/sh

build_dir=$(dirname $0);
ps_16_release_version=1.1.0;
ps_17_release_version=1.1.0;

build_release_file() {
    if [ -z "$1" ]
        then
            echo "Prestashop version wasn't specified";
            return;
    fi

    if [ -z "$2" ]
        then
            echo "Release version wasn't specified";
            return;
    fi

    ps_version=$1;
    release_version=$2;

    echo "Building Prestashop $ps_version..."
    echo "Creating temporary directory"
    rm -rf $build_dir/tawkto
    mkdir $build_dir/tawkto

    echo "Copying files to temporary directory"
    cp -r $build_dir/../prestashop$ps_version/* $build_dir/tawkto/

    echo "Creating zip file"
    (cd $build_dir && zip -9 -rq tawk-prestashop-$ps_version-$release_version.zip tawkto)

    echo "Cleaning up"
    rm -rf $build_dir/tawkto

    echo "Done building Prestashop $ps_version!"
}

build_release_file 1.6 $ps_16_release_version;
build_release_file 1.7 $ps_17_release_version;
