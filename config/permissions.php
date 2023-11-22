<?php

return [
    "permission" => [
        "item" => [
            "module" => "Items",
            "operation" => ["list", "create", "update", "delete"]
        ],
        "tenant" => [
            "module" => "Tenants",
            "operation" => ["list", "create", "update", "delete"]
        ],
        "user" => [
            "module" => "Users",
            "operation" => ["list", "create", "update", "delete"]
        ],
        "permission" => [
            "module" => "Permissions",
            "operation" => ["list", "create", "update", "delete"]
        ],
        "dictionary" => [
            "module" => "Dictionaries",
            "operation" => ["list", "create", "update", "delete"]
        ],
        "status" => [
            "module" => "Statuses",
            "operation" => ["list", "create", "update", "delete"]
        ],
        "stats" => [
            "module" => "Stats",
            "operation" => ["list"]
        ],
    ]
];