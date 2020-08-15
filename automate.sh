#!/bin/bash
mkdir hello
php artisan bb:create-todays-sub-charges  
php artisan bb:bill-members
echo “task complete“



