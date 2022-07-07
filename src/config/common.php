<?php
return [
    'components' => [
        'cms' => [
            'smsHandlers'             => [
                'smsru' => [
                    'class' => \skeeks\cms\callcheck\smsru\SmsruCallcheckHandler::class
                ]
            ]
        ],
    ],
];