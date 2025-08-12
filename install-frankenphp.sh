#!/usr/bin/env bash

set -e

if [ -f frankenphp ]; then
   rm frankenphp
fi

if [[ "$OSTYPE" == "linux-gnu"* ]]; then
    if [[ "$(uname -p)" == "x86_64" ]]; then
        curl -L "https://github.com/dunglas/frankenphp/releases/latest/download/frankenphp-linux-x86_64" -o frankenphp && chmod +x frankenphp
    else
        curl -L "https://github.com/dunglas/frankenphp/releases/latest/download/frankenphp-linux-aarch64" -o frankenphp && chmod +x frankenphp
    fi
elif [[ "$OSTYPE" == "darwin"* ]]; then
    if [[ "$(uname -p)" == "x86_64" ]]; then
        curl -L "https://github.com/dunglas/frankenphp/releases/latest/download/frankenphp-mac-x86_64" -o frankenphp && chmod +x frankenphp
    else
        curl -L "https://github.com/dunglas/frankenphp/releases/latest/download/frankenphp-mac-arm64" -o frankenphp && chmod +x frankenphp
    fi
else
    echo "Manual install."
fi
