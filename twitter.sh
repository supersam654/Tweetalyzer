#!/bin/bash

echo 'Starting Snakes'
nohup ./project.py boca 26 -80 >> /dev/null &
sleep 5s
nohup ./project.py boston 42 -71 >> /dev/null &
sleep 5s
nohup ./project.py nyc 40 -74 >> /dev/null &
sleep 5s
nohup ./project.py sf 37 -122 >> /dev/null &
echo 'Snakes Started'

