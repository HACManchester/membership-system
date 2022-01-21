#!/bin/bash
php artisan bb:check-memberships
php artisan bb:create-todays-sub-charges  
php artisan bb:bill-members
echo “task complete“