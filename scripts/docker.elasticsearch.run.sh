#!/bin/bash
docker run -p 9200:9200 -p 9300:9300 -e "discovery.type=single-node" -e "ES_JAVA_OPTS=-Xms512m -Xmx512m" --name=elasticsearch-gitki -d docker.elastic.co/elasticsearch/elasticsearch:7.17.18
