<?php

return [
    "permission" => [
        "item" => [
            "module" => "Items",
            "operation" => ["list", "create", "update", "delete"]
        ],
        "rent" => [
            "module" => "Rents",
            "operation" => ["list", "create", "update", "delete"]
        ],
        "customer" => [
            "module" => "Customers",
            "operation" => ["list", "create", "update", "delete"]
        ],
        "tenant" => [
            "module" => "Tenants",
            "operation" => ["list", "create", "update", "delete"]
        ],
        "document" => [
            "module" => "Documents",
            "operation" => ["list", "create", "update", "delete"]
        ],
        "fault" => [
            "module" => "Faults",
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
        "config" => [
            "module" => "Configurations",
            "operation" => ["update"]
        ],
        "customer_invoices" => [
            "module" => "Customer invoices",
            "operation" => ["list", "create", "update", "delete"]
        ]
    ]
];