<?php
/**
 * Created by PhpStorm.
 * User: masterx2
 * Date: 04.09.15
 * Time: 14:30
 */

namespace App\Models;


class Link extends ModelAbstract{
    public $schema = [
        'created' => [
            'default' => 'now',
            'value_type' => 'date',
            'control_type' => 'input',
        ],
        'original_url' => [
            'default' => null,
            'value_type' => 'string',
            'control_type' => 'input'
        ],
        'short_url' => [
            'default' => null,
            'value_type' => 'string',
            'control_type' => 'input'
        ],
        'sid' => [
            'default' => null,
            'value_type' => 'string',
            'control_type' => 'input'
        ],
        'click_count' => [
            'default' => 0,
            'value_type' => 'integer',
            'control_type' => 'input'
        ],
        'last_click' => [
            'default' => null,
            'value_type' => 'date',
            'control_type' => 'input'
        ],
        'pin' => [
            'default' => null,
            'value_type' => 'integer',
            'control_type' => 'input'
        ]
    ];

    public $keys = [
        'sid', '_id'
    ];
}