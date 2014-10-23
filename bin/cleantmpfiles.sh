#!/bin/sh
# Efface les fichiers temporaires d'execution des applis CompPhy
#  $1 : repertoire contenant les fichiers a effacer
#  $2 : extension des fichiers a effacer

if [ -n "$1" ] && [ -n "$2" ] ; then
  # Test si des fichiers ayant l'extension sont presents
  ls "$1"/*"$2" >/dev/null 2>&1
  # Efface les fichiers
  if [ $? -eq 0 ] ; then
    rm "$1"/*"$2"
  fi
fi

exit 0
