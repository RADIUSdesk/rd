#! /bin/bash

# This will destroy everything in this dir! except .git and .gitignore

mv .git .gitBACK
mv .gitignore .gitignoreBACK

rm -rf ./rd
rm -rf ./cake2
rm -rf ./cake3
svn checkout svn://dvdwalt@svn.code.sf.net/p/radiusdesk/code/trunk/rd ./rd
mkdir cake2
svn checkout svn://dvdwalt@svn.code.sf.net/p/radiusdesk/code/trunk/rd_cake ./cake2/rd_cake
svn checkout svn://dvdwalt@svn.code.sf.net/p/radiusdesk/code/trunk/cake3 ./cake3

( find . -type d -name ".git" \
&& find . -name ".gitignore" \
&& find . -name ".gitmodules" \
&& find . -name ".svn" \
) | xargs rm -rf

mv .gitBACK .git
mv .gitignoreBACK .gitignore

touch cake2/rd_cake/webroot/files/imagecache/.gitkeep
touch cake3/rd_cake/logs/.gitkeep
touch cake3/rd_cake/tmp/.gitkeep

git status
