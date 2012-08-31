#! /usr/bin/env bash

if [ -z $OMEKA_BRANCH ]; then
  OMEKA_BRANCH=stable-1.5
fi

PLUGIN_DIR=`pwd`
OMEKA_DIR=`pwd`/omeka
SOLR_VERSION="3.6.1"

mysql -e "create database IF NOT EXISTS omeka_test;" -uroot;
git clone https://github.com/omeka/Omeka.git $OMEKA_DIR

# check out the correct branch
cd $OMEKA_DIR && git checkout $OMEKA_BRANCH && git submodule init && git submodule update
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

# symlink the plugin
cd $OMEKA_DIR/plugins && ln -s $PLUGIN_DIR

# Solr set up -- ZOMG
cd $PLUGIN_DIR && wget http://apache.cs.utah.edu/lucene/solr/$SOLR_VERSION/apache-solr-$SOLR_VERSION.tgz && tar xvf apache-solr-$SOLR_VERSION.tgz
sed -i 's/8983/8080/g' $PLUGIN_DIR/apache-solr-$SOLR_VERSION/example/etc/jetty.xml
cd $PLUGIN_DIR/apache-solr-$SOLR_VERSION/example && java -jar -Dsolr.solr.home=$PLUGIN_DIR/solr-home start.jar &

#sudo apt-get install -qq openjdk-7-jdk solr-tomcat

#sudo cat 'export JAVA_HOME=$PATH:/usr/lib/jvm/java-7-openjdk-i186/bin' >> /etc/profile 
#sudo sed -i 's/\usr\/share\/solr/$PLUGIN_DIR\/solr-home/' /etc/tomcat6/Catalina/localhost/solr.xml
#export JAVA_HOME=$PATH:/usr/lib/jvm/java-7-openjdk-i186/bin
#export JAVA_OPTS="$JAVA_OPTS -Dsolr.solr.home=$PLUGIN_DIR/solr-home"
#sudo service tomcat6 restart


