#! /usr/bin/env bash

if [ -z $PLUGIN_DIR ]; then
  PLUGIN_DIR=`pwd`
fi

if [ -z $OMEKA_DIR ]; then
export OMEKA_DIR=`pwd`/omeka
  echo "omeka_dir set"
fi

SOLR_VERSION="3.6.1"

echo "Plugin Directory: $PLUGIN_DIR"
echo "Omeka Directory: $OMEKA_DIR"

echo "\n Starting up Solr..."
cd $PLUGIN_DIR/apache-solr-$SOLR_VERSION/example && java -Djetty.logs=/tmp/jetty.log -Dsolr.solr.home=$PLUGIN_DIR/solr-home -jar start.jar >> /dev/null 2>&1 & 

echo "\n dumping jetty.xml"
cat $PLUGIN_DIR/apache-solr-$SOLR_VERSION/example/etc/jetty.xml

echo "\n Starting tests..."
cd tests/ && phpunit --configuration phpunit_travis.xml --coverage-text
