#!/bin/bash
unameOut="$(uname -s)"
case "${unameOut}" in
    Linux*)     machine=Linux;;
    Darwin*)    machine=Mac;;
    CYGWIN*)    machine=Cygwin;;
    MINGW*)     machine=MinGw;;
    *)          machine="UNKNOWN:${unameOut}"
esac

cp "$3" "$4"
while read -r line; do
  if [[ $line != *" "* ]]; then
    #read line and remove special characters
    platform="${line/$'\r'}"
  else
    if [[ "$1" == "$platform" ]] || [[ "general" == "$platform" ]]; then
      if [[ "$machine" == "Mac" ]]; then
        IFS=':'
        property=($line)
        key=${property[0]##  }
        value="${property[1]/$'\r'}"
        value="${value## }"
        sed -i '' "s:##${key}##:${value}:g" "$4"
      else
        #explode by : and extract key and value
        IFS=':' read -a property <<< $line
        key=${property[0]}
        value="${property[1]/$'\r'}"
        value="${value## }"
        sed -i "s:##${key}##:${value}:g" "$4"
      fi
    fi
  fi
done < "$2"
mv "$4" ../"$4"
