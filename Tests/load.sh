#!/bin/bash
SYMFONY_ENV=${1:-default}
sudo rm -rf /tmp/gitkitest/
mkdir /tmp/gitkitest/
cp -r Tests/Data/repo/ /tmp/gitkitest/
(cd /tmp/gitkitest/repo/ && git init && git config user.email "gitki@dontdrinkandroot.net" && git config user.name "GitKi" && git add -A && git commit -m "Initial commit")
HTTPDUSER=`ps axo user,comm | grep -E '[a]pache|[h]ttpd|[_]www|[w]ww-data|[n]ginx' | grep -v root | head -1 | cut -d\  -f1`
sudo setfacl -R -m u:"$HTTPDUSER":rwX -m u:`whoami`:rwX /tmp/gitkitest/
sudo setfacl -dR -m u:"$HTTPDUSER":rwX -m u:`whoami`:rwX /tmp/gitkitest/
Tests/Utils/console --env=$SYMFONY_ENV assets:install Tests/Utils/Application/web/
if [ $SYMFONY_ENV = "elasticsearch" ]; then
    Tests/Utils/console --env=$SYMFONY_ENV gitki:reindex
fi
