<?php
return [
    'default' => [
        [
             'name' => 'T_IDENT',
             'regex' => '[_a-zA-Z][_a-zA-Z0-9]*'
        ],
        [
            'name' => 'T_OPEN_{',
            'regex' => '{'
        ],
        [
            'name' => 'T_CLOSE_}',
            'regex' => '}'
        ],
        [
            'name' => 'T_OPEN_(',
            'regex' => '\('
        ],
        [
            'name' => 'T_CLOSE_)',
            'regex' => '\)'
        ],
        [
            'name' => 'T_ASSIGN',
            'regex' => ':'
        ],
        [
            'name' => 'T_STRING_START',
            'regex' => "'",
            'context' => 'string1'
        ],
        [
            'name' => 'T_STRING_START',
            'regex' => '"',
            'context' => 'string2'
        ]
        // [
        //     'name' => 'T_COMMENT',
        //     'regex' => '\/\/.*',
        // ],
        // [
        //     'name' => 'T_COMMENT',
        //     'regex' => '\/\*',
        //     'context' => 'comment'
        // ],
        // [
        //     'name' => 'T_NUMBER',
        //     'regex' => '(?:0x)?[\da-f]+'
        // ]
    ],

    // 'class-name' => [
    //     [
    //         'name' => 'T_CLASSNAME',
    //         'regex' => '[_a-zA-Z][_a-zA-Z0-9]*',
    //         'context' => '_back'
    //     ],
    // ],
    //
    // 'ref' => [
    //     [
    //         'name' => 'T_FUNCTION_CALL',
    //         'regex' => '[_a-zA-Z][_a-zA-Z0-9]*(?=\s*\(.*\))',
    //         'context' => '_back'
    //     ],
    //     [
    //          'name' => 'T_REF_VARIABLE',
    //          'regex' => '[_a-zA-Z][_a-zA-Z0-9]*',
    //          'context' => '_back'
    //     ],
    // ],

    // 'comment' => [
    //     [
    //         'name' => 'T_COMMENT',
    //         'regex' => '\*\/',
    //         'context' => '_back'
    //     ],
    //     [
    //         'name' => 'T_COMMENT',
    //         'regex' => '.*(?=\\*\\/)|.*',
    //     ]
    // ],

    'string1' => [
        [
            'name' => 'T_STRING_END',
            'regex' => "'",
            'context' => '_back'
        ],
        [
            'name' => 'T_STRING_ESCAPE',
            'regex' => '\\\\',
            'context' => 'string-escape'
        ],
        [
            'name' => 'T_STRING',
            'regex' => '[^\\\|^\']*',
        ]
    ],

    'string2' => [
        [
            'name' => 'T_STRING_END',
            'regex' => '"',
            'context' => '_back'
        ],
        [
            'name' => 'T_STRING_ESCAPE',
            'regex' => '\\\\',
            'context' => 'string-escape'
        ],
        [
            'name' => 'T_STRING',
            'regex' => '[^\\\|^"]*',
        ]
    ],

    'string-escape' => [
        [
            'name' => 'T_STRING_ESCAPE',
            'regex' => ".",
            'context' => '_back'
        ]
    ]
];
