phpdoc -o HTML:frames:earthli -d ../controllers,../library/CT -f ../library/*php -t ../documentation/apidoc

mkdir ../documentation/manual-html
cd ../documentation/manual-html
docbook2html ../manual/manual.xml

