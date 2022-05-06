<?php

return [
    //所有Redis的host[不区分主从]
    'host'   => [
        '127.0.0.1',
        '127.0.0.1',
        '127.0.0.1',
        '127.0.0.1',
        '127.0.0.1',
        '127.0.0.1',
        '127.0.0.1',
    ],
    //端口 string / array
    'port'   => 6379,
    //密码 string / array
    'auth'   => '',
    //主Redis ID [host中主机对应数组的键]
    'master' => [0, 1, 4, 5],
    //从Redis ID [host中主机对应数组的键]
    'slave'  => [2, 3, 6]
];