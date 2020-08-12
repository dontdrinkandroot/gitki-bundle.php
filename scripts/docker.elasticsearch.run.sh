#!/bin/bash
docker run -p 9200:9200 -p 9300:9300 -e "discovery.type=single-node" --name=elasticsearch-gitki -d docker.elastic.co/elasticsearch/elasticsearch:7.8.1
