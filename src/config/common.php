<?php
return [
    'components' => [
        'cms' => [
            'callcheckHandlers'             => [
                'smsru' => [
                    'class' => \skeeks\cms\callcheck\smsru\SmsruCallcheckHandler::class
                ]
            ]
        ],
    ],
];