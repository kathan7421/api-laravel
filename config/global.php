<?php

return [
        'siteTitle' => 'iss',
        'pagination'=> 20,
        'tagLine' => '',

        "api_url"=>env('API_URL'),

        'storage_url' => env('APP_URL').'/storage/app/public',
        'public_url' => env('APP_URL') . '/public',

        'profile_image' => env('APP_URL').'/storage/app/public/',
        'product_image'=>env('APP_URL').'/storage/app/public/product/',

        // 'home_header_image_path' => env('APP_URL').'/storage/app/public/home_header',
        // 'home_header_image_url'=>env('APP_URL').'/storage/app/public/home_header/',



        // 'profile_image_path' => env('APP_PATH').'/storage/app/public/admin_profile/',
        // 'profile_image_url' => env('APP_URL').'/storage/app/public/admin_profile/',
        
        // 'gkey' => 'AIzaSyDBJkKgE08albjgBB0fJDz56vBZJmnU6lI',
        // 'api_key'=> 'apikey=tE4NkTYaR1phCwEADJY17Ie0rBtmzUEl',
        
        // 'image_path'=> env('APP_PATH').'/storage/app/public/images/',
        // 'image_url' => env('APP_URL').'/storage/app/public/images/',

        // 'Static_image_path'=> env('APP_PATH').'/public/assets/images/',
        // 'Static_image_url' => env('APP_URL').'/public/assets/images/',
        
        'site_url' => env('APP_URL'),
        'SMTP_DETAILS' => [
                'MAIL_DRIVER'=> env('MAIL_DRIVER'),
                'MAIL_HOST'  => env('MAIL_HOST'),
                'MAIL_PORT'  => env('MAIL_PORT'),
                'MAIL_USERNAME' => env('MAIL_USERNAME'),
                'MAIL_PASSWORD' => env('MAIL_PASSWORD'),
                'MAIL_ENCRYPTION' => env('MAIL_ENCRYPTION'),
                'MAIL_FROM_ADDRESS' => env('MAIL_FROM_ADDRESS'),
                'MAIL_FROM_NAME' => env('MAIL_FROM_NAME'),
        ],

        'lang'=>[
                'en'=>'English',
                'ar'=>'Arabic',

        ],
        'orderStatus'=>[
                '1'=>'Pending',
                '2'=>'Process',
                '3'=>'Shipped',
                '4'=>'Completed',
                '5'=>'Cancelled',
            ],
            'statusColor'=>[
                '1'=>'info',
                '2'=>'inverse',
                '3'=>'primary',
                '4'=>'success',
                '5'=>'danger',
            ],


        "user_type"=>[
                '1'=>'Admin',
                '2'=>'Artist',
                '3'=>'Organization',
                '4'=>'Others'
        ],

        "packages_category"=>[
                "1"=>"Free",
                "2"=>"Paid",
        ],

        "package_type"=>[
                "1"=>"Free",
                "2"=>"Paid",
        ],

        "packages_user_type"=>[
                "2"=>'Artist',
                "3"=>'Organization',
                "4"=>'Gallary',
        ]
];
