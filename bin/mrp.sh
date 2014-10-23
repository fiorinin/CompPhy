#!/bin/sh
# Ce script execute MRP pour CompPhy.
# Les paramètres du programme sont contenus
# dans le fichier passé en paramètre
#
# Auteur: Vincent Lefort
#
# arguments:
#   $1: chemin du fichier contenant les
#       paramètres du job
#

# request Bourne shell as shell for job
#$ -S /bin/sh

INPUT=`cat "$1" | grep INPUT | awk -F "::" '{print $2}'`
DIR=`cat $1 | grep DIR | awk -F "::" '{print $2}'`
BINPATH=`cat $1 | grep BINPATH | awk -F "::" '{print $2}'`

export PYTHONPATH="$BINPATH""MRP/nm/lib/python2.6/site-packages/:""$BINPATH""MRP/spruce/lib/python2.6/site-packages/"

cmdstr="python ""$BINPATH""MRP/spruce/bin/makeRatchetFile.py -i ""$INPUT"" -o ""$DIR""mrp.cmd"
eval "$cmdstr"

cd "$DIR"

cmdstr="$BINPATH""paup-linux ""$DIR""mrp.cmd"
eval "$cmdstr"


exit 0

