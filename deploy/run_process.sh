#!/bin/bash

killall -q -9 php
nohup php $1/src/worker.php > /dev/null &

exit 0
