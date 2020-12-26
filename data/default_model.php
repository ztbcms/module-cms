<?php
/**
 * Author: jayinton
 */

return [
    'table'  => [
        'name'    => '',
        'table'   => '',
        'engine'  => 'InnoDB',
        'charset' => 'utf8mb4',
    ],
    'fields' => [
        [
            'name'          => 'ID',
            'field'         => 'id',
            'form_type'     => 'number',
            'field_type'    => 'int',
            'field_length'  => 11, // 长度
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
            'name'          => '分类ID',
            'field'         => 'catid',
            'form_type'     => 'number',
            'field_type'    => 'int',
            'field_length'  => 11,
            'field_is_null' => 0,
            'field_key'     => '',
            'field_extra'   => '',
            'default'       => '0',
            'setting'       => [
                'decimals_amount' => 0,
                'is_unsigned'     => 1
            ]
        ],
        [
            'name'          => '标题',
            'field'         => 'title',
            'form_type'     => 'text',
            'field_type'    => 'varchar',
            'field_length'  => 255,
            'field_is_null' => 0,
            'field_key'     => '',
            'field_extra'   => '',
            'default'       => '',
            'setting'       => []
        ],
        [
            'name'          => '关键字',
            'field'         => 'keywords',
            'form_type'     => 'text',
            'field_type'    => 'varchar',
            'field_length'  => 255,
            'field_is_null' => 0,
            'field_key'     => '',
            'field_extra'   => '',
            'default'       => '',
            'setting'       => []
        ],
        [
            'name'          => '描述',
            'field'         => 'description',
            'form_type'     => 'text',
            'field_type'    => 'varchar',
            'field_length'  => 255,
            'field_is_null' => 0,
            'field_key'     => '',
            'field_extra'   => '',
            'default'       => '',
            'setting'       => []
        ],
        [
            'name'          => '缩略图',
            'field'         => 'thumb',
            'form_type'     => 'text',
            'field_type'    => 'varchar',
            'field_length'  => 255,
            'field_is_null' => 0,
            'field_key'     => '',
            'field_extra'   => '',
            'default'       => '',
            'setting'       => []
        ],
        [
            'name'          => '序号',
            'field'         => 'order',
            'form_type'     => 'number',
            'field_type'    => 'int',
            'field_length'  => 11,
            'field_is_null' => 0,
            'field_key'     => '',
            'field_extra'   => '',
            'default'       => '',
            'setting'       => [
                'decimals_amount' => 0,
                'is_unsigned'     => 1
            ]
        ],
        [
            'name'          => '状态',// 99审核通过 1待审核 0审核不通过
            'field'         => 'status',
            'form_type'     => 'radio',
            'field_type'    => 'varchar',
            'field_length'  => 2,
            'field_is_null' => 0,
            'field_key'     => '',
            'field_extra'   => '',
            'default'       => '1',
            'setting'       => []
        ],
        [
            'name'          => '用户ID',
            'field'         => 'user_id',
            'form_type'     => 'text',
            'field_type'    => 'varchar',
            'field_length'  => 255,
            'field_is_null' => 0,
            'field_key'     => '',
            'field_extra'   => '',
            'default'       => '',
            'setting'       => []
        ],
        [
            'name'          => '管理员ID',
            'field'         => 'admin_id',
            'form_type'     => 'text',
            'field_type'    => 'varchar',
            'field_length'  => 255,
            'field_is_null' => 0,
            'field_key'     => '',
            'field_extra'   => '',
            'default'       => '',
            'setting'       => []
        ],
        [
            'name'          => '添加时间',
            'field'         => 'create_time',
            'form_type'     => 'number',
            'field_type'    => 'int',
            'field_length'  => 11,
            'field_is_null' => 0,
            'field_key'     => '',
            'field_extra'   => '',
            'default'       => '',
            'setting'       => []
        ],
        [
            'field'         => 'update_time',
            'name'          => '更新时间',
            'form_type'     => 'number',
            'field_type'    => 'int',
            'field_length'  => 11,
            'field_is_null' => 0,
            'field_key'     => '',
            'field_extra'   => '',
            'default'       => '',
            'setting'       => []
        ],

    ]
];