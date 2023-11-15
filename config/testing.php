<?php

return [
    'accounts' => [
        [
            'email' => 'arturpatura@gmail.com',
            'data' => [
                'firstname' => 'Artur',
                'lastname' => 'Patura',
                'phone' => '723310782',
                'firm_identifier' => 'netextend.pl',
                'password' => 'Pass102@',
                'password_confirmation' => 'Pass102@'
            ],
            'workers' => [
                [
                    'firstname' => 'Jan',
                    'lastname' => 'Kowalski',
                    'email' => 'jan-kowalski@gmail.com',
                    'phone' => '879546254',
                    'superuser' => false,
                    'password' => 'Pass102@',
                    'password_confirmation' => 'Pass102@',
                ],
                [
                    'firstname' => 'Grzegorz',
                    'lastname' => 'Wąs',
                    'email' => 'grzegorz-was@gmail.com',
                    'phone' => '125963544',
                    'superuser' => true,
                    'password' => 'Pass102@',
                    'password_confirmation' => 'Pass102@',
                ],
                [
                    'firstname' => 'Zbigniew',
                    'lastname' => 'Nowak',
                    'email' => 'zbigniew-nowak@gmail.com',
                    'phone' => '878555415',
                    'superuser' => false,
                    'password' => 'Pass102@',
                    'password_confirmation' => 'Pass102@',
                ],
                [
                    'firstname' => 'Jan',
                    'lastname' => 'Byk',
                    'email' => 'a.rturpatura@gmail.com',
                    'phone' => '878555415',
                    'superuser' => false,
                    'password' => 'Pass102@',
                    'password_confirmation' => 'Pass102@',
                ],
            ],
            'items' => [
                [
                    'type' => 'estate',
                    'active' => 1,
                    'name' => 'Estate #1',
                    'street' => 'Example street',
                    'house_no' => '13',
                    'apartment_no' => null,
                    'city' => 'Breslau',
                    'zip' => '00-001',
                ],
                [
                    'type' => 'estate',
                    'active' => 1,
                    'name' => 'Estate #2',
                    'street' => 'Example street',
                    'house_no' => '1',
                    'apartment_no' => null,
                    'city' => 'Warsaw',
                    'zip' => '00-001',
                ]
            ]
        ],
        [
            'email' => 'a.rturpatura@gmail.com',
            'data' => [
                'firstname' => 'Jan',
                'lastname' => 'Kowalski',
                'phone' => '723310783',
                'firm_identifier' => 'Firma Pana Jana',
                'password' => 'Pass102@',
                'password_confirmation' => 'Pass102@'
            ],
            'workers' => [
                [
                    'firstname' => 'Błażej',
                    'lastname' => 'Wąsik',
                    'email' => 'blazej-wasik@gmail.com',
                    'phone' => '879546267',
                    'superuser' => false,
                    'password' => 'Pass102@',
                    'password_confirmation' => 'Pass102@',
                ],
                [
                    'firstname' => 'Krystyna',
                    'lastname' => 'Nowak',
                    'email' => 'nowak-krystyna@gmail.com',
                    'phone' => '125962345',
                    'superuser' => true,
                    'password' => 'Pass102@',
                    'password_confirmation' => 'Pass102@',
                ],
                [
                    'firstname' => 'Artur',
                    'lastname' => 'Patura - pracownik',
                    'email' => 'arturpatura@gmail.com',
                    'phone' => '125962345',
                    'superuser' => false,
                    'password' => 'Pass102@',
                    'password_confirmation' => 'Pass102@',
                ]
            ],
            'items' => [
                [
                    'type' => 'estate',
                    'active' => 1,
                    'name' => 'Estate #3',
                    'street' => 'Example street',
                    'house_no' => '13',
                    'apartment_no' => null,
                    'city' => 'Poznań',
                    'zip' => '00-001',
                ],
                [
                    'type' => 'estate',
                    'active' => 1,
                    'name' => 'Estate #4',
                    'street' => 'Example street',
                    'house_no' => '13',
                    'apartment_no' => null,
                    'city' => 'Bydgoszcz',
                    'zip' => '00-001',
                ]
            ]
        ],
        [
            'email' => 'ar.turpatura@gmail.com',
            'data' => [
                'firstname' => 'Mariusz',
                'lastname' => 'Bąk',
                'phone' => '723310784',
                'firm_identifier' => 'Bąk i Synowie',
                'password' => 'Pass102@',
                'password_confirmation' => 'Pass102@'
            ],
            'workers' => [
                [
                    'firstname' => 'Artur',
                    'lastname' => 'Patura - pracownik',
                    'email' => 'arturpatura@gmail.com',
                    'phone' => '125962345',
                    'superuser' => false,
                    'password' => 'Pass102@',
                    'password_confirmation' => 'Pass102@',
                ],
                [
                    'firstname' => 'Artur',
                    'lastname' => 'Patura - pracownik do sprawdzania uprawnień',
                    'email' => 'art.urpa.tura@gmail.com',
                    'phone' => '125962345',
                    'superuser' => false,
                    'password' => 'Pass102@',
                    'password_confirmation' => 'Pass102@',
                ]
            ],
            'items' => [
                [
                    'type' => 'estate',
                    'active' => 1,
                    'name' => 'Estate #5',
                    'street' => 'Example street',
                    'house_no' => '13',
                    'apartment_no' => null,
                    'city' => 'Kraków',
                    'zip' => '00-001',
                ],
                [
                    'type' => 'estate',
                    'active' => 1,
                    'name' => 'Estate #6',
                    'street' => 'Example street',
                    'house_no' => '13',
                    'apartment_no' => null,
                    'city' => 'Wrocław',
                    'zip' => '00-001',
                ]
            ]
        ],
    ]
];