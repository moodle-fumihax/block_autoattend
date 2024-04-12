#!/bin/bash

./setup_jbxl

rm -rf .git .gitignore jbxl/.git ibxl/.gitignore

(cd ..; zip -r block_autoattend.zip block_autoattend/)

