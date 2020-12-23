<?php
/**
 * Author: jayinton
 */

return [
    'table' => [
        'table'   => '',
        'engine'  => 'InnoDB',
        'charset' => 'utf8mb4',
        'comment' => '',
    ],
    'fields' => [
        [
            'field'   => 'id',
            'type'    => 'int(11) unsigned',
            'default' => '',
            'null'    => false,
            'comment' => 'ID',
            'key' => 'PRI', // PRI => PRIMARY KEY, UNI => UNIQUE KEY, MUL=>KEY
            'extra' => 'AUTO_INCREMENT',
        ]
    ]
];