#!/bin/bash
if [[ ! -d public_html/libraries ]]; then
  yarn install --force --modules-folder ./public_html/libraries
fi; 

cd public_html/src
if [[ ! -d node_modules/rollup ]]; then
  yarn
fi; 
yarn run compile-all
cd ../..
