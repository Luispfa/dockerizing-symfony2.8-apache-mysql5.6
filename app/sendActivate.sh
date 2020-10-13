#!/bin/bash
PHP_EXECUTABLE="/usr/bin/php"
SCRIPT_DIRECTORY="/srv/web/web_lugh_logic/host/lugh002/logic/app"
SCRIPT_NAME='console send:activate --env=prod'
if ps -fea | grep "console send:activate --env=prod" | grep -v grep
then
    date
    echo $(date +%Y%m%d_%H%M%S_%N | sed 's,.\{6\}$,,') "$@ ";
    echo "Overlaping message end"
else
    cd $SCRIPT_DIRECTORY
    $PHP_EXECUTABLE $SCRIPT_DIRECTORY"/"$SCRIPT_NAME
fi
