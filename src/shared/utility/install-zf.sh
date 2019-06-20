#!/bin/bash

KAS_USER="ivar"			# Should be www-data or user used with suPHP
CURRENT_DIR=$(pwd)

echo -n "Checking for ZendFramework... "
if [ ! -d $CURRENT_DIR/../library/Zend ]; then
	echo "not found (installing)"

	TMPFILE=$(mktemp)
	URL="http://framework.zend.com/releases/ZendFramework-1.7.1/ZendFramework-1.7.1.tar.gz"

	wget $URL -O $TMPFILE

	TMPDIR=$(mktemp -d)

	cd $TMPDIR
	tar -zxvf $TMPFILE -C $TMPDIR

	mv ZendFramework-1.7.1/library/Zend $CURRENT_DIR/../library/

	cd $CURRENT_DIR

	rm $TMPFILE
	rm -rf $TMPDIR
else
	echo "found"
fi

