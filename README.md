# Framework PHP

FuelPhp (Similar Laravel)
https://fuelphp.com/docs/

# Import db

Search repository db/dbsaifty.sql

Import db

# Config credentials db

path:  fuel/app/config/development/db.php

return array(
    'default' => array(
        'connection'  => array(
            'dsn'        => 'mysql:host=localhost;dbname=yourdb',
            'username'   => '',
            'password'   => '',
        ),
    ),
);

# Import requests postman

Search repository postman/apisaifty-endpoints.postman_collection.json

Import endpoints in postman

# Code breaks

path: fuel/app/classes/controller/breaks.php

method: post_create
create a break

Endpoint
POST /public/index.php/breaks/create.json

Header
Authorization (String, required)

Params
message (String, required), picture (file)

# Test

Do requests with postman

