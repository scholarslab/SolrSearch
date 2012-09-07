#! /usr/bin/env bash

if [ -z $PLUGIN_DIR ]; then
  PLUGIN_DIR=`pwd`
fi

if [ -z $OMEKA_DIR ]; then
  OMEKA_DIR=`pwd`/omeka
fi

SOLR_PORT="8080"

function solr_responding {
  curl -o /dev/null "http://localhost:$SOLR_PORT/solr/admin/ping" > /dev/null 2>&1
}

function wait_until_solr_responds {
  while ! solr_responding; do
    printf "%s" "."
    sleep 1
  done
}

function start_solr {
  echo "Starting up Solr..."

  if [ -f sunspot-solr.pid ]; then 
    bundle exec sunspot-solr stop || true; 
  fi

  bundle exec sunspot-solr start -p 8080 -d $PLUGIN_DIR/solr-home

}

function run_tests {
  echo ""
  echo "Running tests..."

  cd $PLUGIN_DIR/tests/ && phpunit --configuration phpunit_travis.xml --coverage-text
}

function stop_solr {
  echo "Stopping Solr..."
  bundle exec sunspot-solr stop
  echo "done."
}

function main {
  echo "Plugin Directory: $PLUGIN_DIR"
  echo "Omeka Directory: $OMEKA_DIR"

  start_solr
  wait_until_solr_responds

  run_tests

  echo ""
  echo "solr is running..."

  stop_solr

}


main
