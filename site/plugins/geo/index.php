<?php

Kirby::plugin('distantnative/field-geo', [
    'fields' => [
        'geo' => [
            'props' => [
                'value' => function ($value = null) {
                    return Yaml::decode($value);
                }
            ],
            'methods' => [
                'toString' => function ($value) {
                    return Yaml::encode($value);
                }
            ]
        ]
    ],
    'fieldMethods' => [
        'lat' => function ($field) {
            return Yaml::decode($field)['lat'];
        },
        'lng' => function ($field) {
            return Yaml::decode($field)['lng'];
        },
        'toMap' => function ($field) {
            $latlng = Yaml::decode($field);
            return `<iframe frameborder="0" style="border:0" src="https://www.google.com/maps/embed/v1/place?key=AIzaSyAe7qYXp9ZiU0WniGiNBBeYclAo4tc5yY0&location={$latlng['lat']},{$latlng['lng']}" allowfullscreen></iframe>`;
        },
    ]
]);
