<?php

namespace Madsoft\Library\App;

use Madsoft\Library\Router;

return $routes = [
    'protected' => [
        'GET' => [
            'my-contents' => [
                'overrides' => [
                    'table-list' => [
                        'columns' => [
                            /* TODO !@#    1000 => */[
                                'text' => 'Scripts',
                                'field' => null,
                                'actions' => [
                                    [
                                        'type' => 'link',
                                        'title' => 'Go to scripts of {{ title }}',
                                        'text' => 'Scripts..',
                                        'href' => '?' . Router::ROUTE_QUERY_KEY .
                                            '=my-scripts/list&content[id]={{ id }}',
                                        'fields' => ['id', 'title'],
                                    ],
                                ],
                            ]
                        ],
                    ],
                ],
            ],
            'my-scripts/list' => [
                
            ],
        ],
    ],
];