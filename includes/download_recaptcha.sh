#!/bin/bash

OLDDIR=$( pwd )
DIR=$( dirname "$0" )

cd "$DIR"

git clone https://github.com/google/recaptcha.git

cd "$OLDDIR"

