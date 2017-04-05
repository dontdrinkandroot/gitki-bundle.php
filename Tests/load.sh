#!/bin/bash
sudo rm -rf /tmp/gitkitest/
mkdir /tmp/gitkitest/
cp -r Tests/Data/repo/ /tmp/gitkitest/
HTTPDUSER=`ps axo user,comm | grep -E '[a]pache|[h]ttpd|[_]www|[w]ww-data|[n]ginx' | grep -v root | head -1 | cut -d\  -f1`
sudo setfacl -R -m u:"$HTTPDUSER":rwX -m u:`whoami`:rwX /tmp/gitkitest/
sudo setfacl -dR -m u:"$HTTPDUSER":rwX -m u:`whoami`:rwX /tmp/gitkitest/
Tests/console --env=default assets:install Tests/Functional/web/
