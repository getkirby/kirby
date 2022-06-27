<?php

$time = random_int(1000000000, 2000000000);

$kirby->response()->expires($time);
echo $time;
