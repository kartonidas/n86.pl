<?php

    return [
        "allowed" => [
            "p1" => [
                "type" => "subscription",
                "name" => "premium:1",
                "price_vat" => 9,
                "price_day_vat" => 0.3,
                "vat" => 23,
                "price" => 7.3171,
                "price_day" => 0.2439,
                "months" => 1,
            ],
            "p12" => [
                "type" => "subscription",
                "name" => "premium:12",
                "price_vat" => 99,
                "price_day_vat" => 0.27,
                "vat" => 23,
                "price" => 80.4878,
                "price_day" => 0.2195,
                "months" => 12,
            ]
        ],
        "free" => [
            "items" => 2,
        ]
    ];