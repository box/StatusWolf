#!/usr/bin/env bash

WWW_USER='apache'

echo -n "Which user does your web server run as? [apache]: "
read USERIN

if [ ! -z $USERIN ]; then
    WWW_USER=$USERIN
fi

WWW_GROUP=$(id -gn ${WWW_USER})

mkdir app/log
chown ${WWW_USER}:${WWW_GROUP} app/log
chown -R ${WWW_USER}:${WWW_GROUP} conf

cd app/static/js/lib

# Get JQuery
/usr/bin/wget http://code.jquery.com/jquery-1.11.1.min.js
ln -s jquery-1.11.1.min.js jquery.js
ln -s jquery-1.11.1.min.js jquery.min.js

# Get JQuery DataTables
/usr/bin/wget http://datatables.net/releases/DataTables-1.10.0.zip
/usr/bin/unzip DataTables-1.10.0.zip DataTables-1.10.0/media/js/jquery.dataTables.min.js
mv DataTables-1.10.0/media/js/jquery.dataTables.min.js .
rm -rf DataTables-1.10.0
rm DataTables-1.10.0.zip

# Get MagnificPopup
/usr/bin/wget --no-check-certificate https://raw.githubusercontent.com/dimsemenov/Magnific-Popup/master/dist/jquery.magnific-popup.min.js
ln -s jquery.magnific-popup.min.js magnific-popup.js

# Get JQuery Autocomplete
/usr/bin/wget --no-check-certificate https://raw.githubusercontent.com/devbridge/jQuery-Autocomplete/master/dist/jquery.autocomplete.min.js
ln -s jquery.autocomplete.min.js jquery.autocomplete.js

# Get Bootstrap
/usr/bin/wget http://getbootstrap.com/2.3.2/assets/bootstrap.zip
/usr/bin/unzip bootstrap.zip bootstrap/js/bootstrap.min.js
mv bootstrap/js/bootstrap.min.js .
rm -rf bootstrap
rm bootstrap.zip
ln -s bootstrap.min.js bootstrap.js

# Get DateJS
/usr/bin/wget https://storage.googleapis.com/google-code-archive-downloads/v2/code.google.com/datejs/date.js
