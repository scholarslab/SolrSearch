#! /usr/bin/env bash

if [ -z $PLUGIN_DIR ]; then
  PLUGIN_DIR=`pwd`
fi

if [ -z $OMEKA_DIR ]; then
  OMEKA_DIR=`pwd`/omeka
fi

SOLR_VERSION="3.6.1"
JETTY_HOME="$PLUGIN_DIR/apache-solr-$SOLR_VERSION/example"
JETTY_CONSOLE="/dev/null"
JETTY_ARGS=""
JAVA_OPTIONS="-Djetty.port=8080 -Dsolr.solr.home=$PLUGIN_DIR/solr-home"
RUN_CMD="cd $JETTY_HOME && java $JAVA_OPTIONS -jar start.jar $JETTY_ARGS"

SOLR_PORT="8080"
#RUN_CMD="java $JAVA_OPTIONS -jar $JETTY_HOME/start.jar $JETTY_ARGS"

function startJetty {
#echo $RUN_CMD
nohup sh -c "$RUN_CMD" &
#nohup sh -c "exec $RUN_CMD >>$JETTY_CONSOLE 2>&1" >/dev/null &
}

function solr_responding {
  curl -o /dev/null "http://localhost:$SOLR_PORT/solr/admin/ping" > /dev/null 2>&1
}

function wait_until_solr_responds {
while ! solr_responding; do
  printf "%s" "."
  sleep 1
done
}

function main {
  echo "Plugin Directory: $PLUGIN_DIR"
  echo "Omeka Directory: $OMEKA_DIR"

  echo "Starting up Solr..."

  if [ -f sunspot-solr.pid ]; then 
    bundle exec sunspot-solr stop || true; 
  fi

  bundle exec sunspot-solr start -p 8080 -d $PLUGIN_DIR/solr-home

  wait_until_solr_responds
  echo "solr is running..."

  echo "Stopping Solr..."
  bundle exec sunspot-solr stop
  echo "done."

  returnvalue=?
  exit $returnvalue

}


main


#cd $PLUGIN_DIR/apache-solr-$SOLR_VERSION/example && java -Djetty.logs=/tmp/jetty.log -Dsolr.solr.home=$PLUGIN_DIR/solr-home -jar start.jar >> /dev/null 2>&1 & 

#startJetty


echo "solr is up."


#cat nohup.out

echo "Starting tests..."
#curl "http://localhost:8080/solr/admin/ping"
#cd $PLUGIN_DIR/tests/ && phpunit --configuration phpunit_travis.xml --coverage-text
