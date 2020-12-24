<?php
/**
 * Author: jayinton
 */

// field_field:  PRI => PRIMARY KEY, UNI => UNIQUE KEY, MUL=>KEY
// field_extra:  AUTO_INCREMENT
return [
    'table'  => [
        'name'      => '',
        'tablename' => '',
        'engine'    => 'InnoDB',
        'charset'   => 'utf8mb4',
    ],
    'fields' => [
        [
            'field'         => 'id',
            'name'          => 'ID',
            'type'          => 'number',
            'field_type'    => 'int',
            'length'        => 11, // 长度
            'field_is_null' => 0, // 是否允许为NULL, 0不允许 1允许
            'field_key'     => 'PRI', // PRI => PRIMARY KEY, UNI => UNIQUE KEY, MUL=>KEY
            'field_extra'   => 'AUTO_INCREMENT', // AUTO_INCREMENT
            'default'       => '',
            'setting'       => [
                'decimals_amount' => 0,
                'is_unsigned'     => 1
            ]
        ],
        [
            'field'         => 'catid',
            'name'          => '分类ID',
            'type'          => 'number',
            'field_type'    => 'int',
            'length'        => 11,
            'field_is_null' => 0,
            'field_key'     => '',
            'field_extra'   => '',
            'default'       => '0',
            'setting'       => [
                'decimals_amount' => 0,
                'is_unsigned' => 1
            ]
        ],
        [
            'field'         => 'title',
            'name'          => '标题',
            'type'          => 'text',
            'field_type'    => 'varchar',
            'length'        => 255,
            'field_is_null' => 0,
            'field_key'     => '',
            'field_extra'   => '',
            'default'       => '',
            'setting'       => []
        ],
        [
            'field'         => 'keywords',
            'name'          => '关键字',
            'type'          => 'text',
            'field_type'    => 'varchar',
            'length'        => 255,
            'field_is_null' => 0,
            'field_key'     => '',
            'field_extra'   => '',
            'default'       => '',
            'setting'       => []
        ],
        [
            'field'         => 'description',
            'name'          => '描述',
            'type'          => 'text',
            'field_type'    => 'varchar',
            'length'        => 255,
            'field_is_null' => 0,
            'field_key'     => '',
            'field_extra'   => '',
            'default'       => '',
            'setting'       => []
        ],
        [
            'field'         => 'thumb',
            'name'          => '缩略图',
            'type'          => 'text',
            'field_type'    => 'varchar',
            'length'        => 255,
            'field_is_null' => 0,
            'field_key'     => '',
            'field_extra'   => '',
            'default'       => '',
            'setting'       => []
        ],
        [
            'field'         => 'order',
            'name'          => '序号',
            'type'          => 'number',
            'field_type'    => 'int',
            'length'        => 11,
            'field_is_null' => 0,
            'field_key'     => '',
            'field_extra'   => '',
            'default'       => '',
            'setting'       => [
                'decimals_amount' => 0,
                'is_unsigned' => 1
            ]
        ],
        [
            'field'         => 'status',
            'name'          => '状态状态 99审核通过 1待审核 0审核不通过',
            'type'          => 'radio',
            'field_type'    => 'varchar',
            'length'        => 2,
            'field_is_null' => 0,
            'field_key'     => '',
            'field_extra'   => '',
            'default'       => '1',
            'setting'       => []
        ],
        [
            'field'         => 'user_id',
            'name'          => '用户ID',
            'type'          => 'text',
            'field_type'    => 'varchar',
            'length'        => 255,
            'field_is_null' => 0,
            'field_key'     => '',
            'field_extra'   => '',
            'default'       => '',
            'setting'       => []
        ],
        [
            'field'         => 'admin_id',
            'name'          => '管理员ID',
            'type'          => 'text',
            'field_type'    => 'varchar',
            'length'        => 255,
            'field_is_null' => 0,
            'field_key'     => '',
            'field_extra'   => '',
            'default'       => '',
            'setting'       => []
        ],
        [
            'field'         => 'create_time',
            'name'          => '添加时间',
            'type'          => 'number',
            'field_type'    => 'int',
            'length'        => 11,
            'field_is_null' => 0,
            'field_key'     => '',
            'field_extra'   => '',
            'default'       => '',
            'setting'       => []
        ],
        [
            'field'         => 'update_time',
            'name'          => '更新时间',
            'type'          => 'number',
            'field_type'    => 'int',
            'length'        => 11,
            'field_is_null' => 0,
            'field_key'     => '',
            'field_extra'   => '',
            'default'       => '',
            'setting'       => []
        ],

    ]
];