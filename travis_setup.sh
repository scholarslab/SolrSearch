#! /usr/bin/env bash

if [ -z $OMEKA_BRANCH ]; then
  OMEKA_BRANCH=stable-1.5
fi

export PLUGIN_DIR=`pwd`
export OMEKA_DIR=`pwd`/omeka

SOLR_VERSION=3.6.1


mysql -e "create database IF NOT EXISTS omeka_test;" -uroot;
git clone https://github.com/omeka/Omeka.git $OMEKA_DIR

# check out the correct branch
cd $OMEKA_DIR && git checkout $OMEKA_BRANCH
cd $PLUGIN_DIR

# move configuration files
mv $OMEKA_DIR/application/config/config.ini.changeme $OMEKA_DIR/application/config/config.ini
mv $OMEKA_DIR/application/tests/config.ini.changeme $OMEKA_DIR/application/tests/config.ini

# set up testing config
sed -i 's/db.host = ""/db.host = "localhost"/' $OMEKA_DIR/application/tests/config.ini
sed -i 's/db.username = ""/db.username = "root"/' $OMEKA_DIR/application/tests/config.ini
sed -i 's/db.dbname = ""/db.dbname = "omeka_test"/' $OMEKA_DIR/application/tests/config.ini
sed -i 's/email.to = ""/email.to = "test@example.com"/' $OMEKA_DIR/application/tests/config.ini
sed -i 's/email.administator = ""/email.administrator = "admin@example.com"/' $OMEKA_DIR/application/tests/config.ini
sed -i 's/paths.maildir = ""/paths.maildir = "\/tmp"/' $OMEKA_DIR/application/tests/config.ini
sed -i 's/paths.imagemagick = ""/paths.imagemagick = "\/usr\/bin\/"/' $OMEKA_DIR/application/tests/config.ini
sed -i 's/256M/512M/' $OMEKA_DIR/application/tests/bootstrap.php

# Solr set up -- ZOMG
cd $PLUGIN_DIR && wget http://apache.cs.utah.edu/lucene/solr/$SOLR_VERSION/apache-solr-$SOLR_VERSION.tgz && tar xvf apache-solr-$SOLR_VERSION.tgz
sed -i  's/8983/8080/g' $PLUGIN_DIR/apache-solr-$SOLR_VERSION/example/etc/jetty.xml
cd $PLUGIN_DIR/apache-solr-$SOLR_VERSION/example && java -jar -Dsolr.solr.home=$PLUGIN_DIR/solr-home start.jar &

# symlink the plugin
cd $OMEKA_DIR/plugins && ln -s $PLUGIN_DIR
