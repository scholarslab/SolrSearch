#! /usr/bin/env bash

if [ -z $PLUGIN_DIR ]; then
  PLUGIN_DIR=`pwd`
fi

if [ -z $OMEKA_DIR ]; then
export OMEKA_DIR=`pwd`/omeka
  echo "omeka_dir set"
fi

SOLR_VERSION="3.6.1"
JETTY_HOME="$PLUGIN_DIR/apache-solr-$SOLR_VERSION/example"
JETTY_CONSOLE="/dev/null"
JETTY_ARGS=""
JETTY_PORT="8080"
JAVA_OPTIONS="-Djetty.port=8080 -Dsolr.solr.home=$PLUGIN_DIR/solr-home"

RUN_CMD="cd $JETTY_HOME && java $JAVA_OPTIONS -jar start.jar $JETTY_ARGS"

function startJetty {
  echo $RUN_CMD
  #`$RUN_CMD &`
  nohup sh -c "exec $RUN_CMD >>$JETTY_CONSOLE 2>&1" >/dev/null &
}

echo "Plugin Directory: $PLUGIN_DIR"
echo "Omeka Directory: $OMEKA_DIR"

echo "\n Starting up Solr..."
#cd $PLUGIN_DIR/apache-solr-$SOLR_VERSION/example && java -Djetty.logs=/tmp/jetty.log -Dsolr.solr.home=$PLUGIN_DIR/solr-home -jar start.jar >> /dev/null 2>&1 & 

startJetty

echo "Starting tests..."
#curl "http://localhost:8080/solr/admin/ping"
cd tests/ && phpunit --configuration phpunit_travis.xml --coverage-text
