<?php

##########################
# Home
##########################

Route::get('/', array('as' => 'home', 'uses' => 'HomeController@index'));


##########################
# Authentication
##########################

Route::get('login', ['as' => 'login', 'uses' => 'SessionController@create']);
Route::get('logout', ['as' => 'logout', 'uses' => 'SessionController@destroy']);
Route::get('gift', ['as' => 'gift', 'uses' => 'GiftController@index']);
Route::get('admin', ['as' => 'admin', 'uses' => 'AdminController@index', 'middleware' => 'role:admin']);
Route::resource('session', 'SessionController', ['only' => ['create', 'store', 'destroy']]);
Route::get('password/forgotten', ['as' => 'password-reminder.create', 'uses' => 'ReminderController@create']);
Route::post('password/forgotten', ['as' => 'password-reminder.store', 'uses' => 'ReminderController@store']);
Route::get('password/reset/{token}', ['as' => 'password.reset', 'uses' => 'ReminderController@getReset']);
Route::post('password/reset', ['as' => 'password.reset.complete', 'uses' => 'ReminderController@postReset']);

Route::get('sso/login', ['uses' => 'SessionController@sso_login']);


##########################
# Component Zoo
##########################

Route::inertia('component-zoo', 'ComponentZoo/Index');


##########################
# Account
##########################

Route::get('account/trusted_missing_photos', ['uses' => 'AccountController@trustedMissingPhotos', 'as' => 'account.trusted_missing_photos', 'middleware' => 'role:admin']);
Route::resource('account', 'AccountController');

//Editing the profile
Route::get('account/{account}/profile/edit', ['uses' => 'ProfileController@edit', 'as' => 'account.profile.edit', 'middleware' => 'role:member']);
Route::put('account/{account}/profile', ['uses' => 'ProfileController@update', 'as' => 'account.profile.update', 'middleware' => 'role:member']);

//Short register url
Route::get('register', ['as' => 'register', 'uses' => 'AccountController@create']);
Route::get('online-only', ['as' => 'online-only', 'uses' => 'AccountController@createOnlineOnly']);

//Special account editing routes
Route::put('account/{account}/alter-subscription', ['as' => 'account.alter-subscription', 'uses' => 'AccountController@alterSubscription', 'middleware' => 'role:admin']);
Route::put('account/{account}/admin-update', ['as' => 'account.admin-update', 'uses' => 'AccountController@adminUpdate', 'middleware' => 'role:admin']);
Route::get('account/confirm-email/send', ['as' => 'account.send-confirmation-email', 'uses' => 'AccountController@sendConfirmationEmail']);
Route::get('account/confirm-email/{id}/{hash}', ['as' => 'account.confirm-email', 'uses' => 'AccountController@confirmEmail']);

//Balance
Route::get('account/{account}/balance', ['uses' => 'BalanceController@index', 'as' => 'account.balance.index', 'middleware' => 'role:member']);

//Inductions
Route::get('general_induction', ['uses' => 'GeneralInductionController@show', 'as' => 'general-induction.show', 'middleware' => 'role:member']);
Route::put('general_induction', ['uses' => 'GeneralInductionController@update', 'as' => 'general-induction.update', 'middleware' => 'role:member']);

// Leaderboards!
Route::get('leaderboard', ['uses' => 'LeaderboardController@index', 'as' => 'leaderboard.index', 'middleware' => 'role:member']);

// Tracked links
Route::get('links/forum', ['uses' => 'LinksController@forum', 'as' => 'links.forum', 'middleware' => 'role:member']);

##########################
# Public Member List
##########################

Route::group(array('middleware' => 'role:member'), function () {
    Route::resource('members', 'MembersController', ['only' => ['index', 'show',]]);
});


##########################
# Newsletter
##########################

Route::get('newsletter', ['uses' => 'NewsletterController@index', 'as' => 'newsletter', 'middleware' => 'role:admin']);


##########################
# Subscriptions / Payments
##########################

Route::get('account/{account}/subscription/store', ['as' => 'account.subscription.store', 'uses' => 'SubscriptionController@store']);
Route::resource('account.subscription', 'SubscriptionController', ['except' => ['store', 'update', 'edit', 'show', 'index']]);

// Exempt from CSRF checks in BB\Http\Middleware\VerifyCsrfToken
Route::post('gocardless/webhook', ['uses' => 'GoCardlessWebhookController@receive']);

Route::post('account/{account}/payment', ['uses' => 'PaymentController@store', 'as' => 'account.payment.store', 'middleware' => 'role:admin']);

Route::group(array('middleware' => 'role:finance'), function () {
    Route::resource('payments', 'PaymentController', ['only' => ['index', 'destroy', 'update']]);
    Route::get('payments/overview', ['uses' => 'PaymentOverviewController@index', 'as' => 'payments.overview']);
    Route::get('payments/balances', ['uses' => 'PaymentBalancesController@index', 'as' => 'payments.balances']);
    Route::get('payments/sub-charges', ['as' => 'payments.sub-charges', 'uses' => 'SubscriptionController@listCharges']);
    Route::get('payments/possible-duplicates', ['as' => 'payments.possible-duplicates', 'uses' => 'PaymentController@possibleDuplicates']);
});

Route::post('account/{account}/payment/create', ['as' => 'account.payment.create', 'uses' => 'PaymentController@create']);
Route::post('account/{account}/update-sub-payment', ['as' => 'account.update-sub-payment', 'uses' => 'AccountController@updateSubscriptionAmount']);

# Payment provider specific urls
Route::post('account/{account}/payment/gocardless', ['as' => 'account.payment.gocardless.create', 'uses' => 'GoCardlessPaymentController@create']);
Route::post('payment/gocardless/{payment}/cancel', [
    'as' => 'payment.gocardless.cancel',
    'uses' => 'GoCardlessPaymentController@cancel',
    'middleware' => 'role:finance'
]);


//Cash
Route::group(array('middleware' => 'role:admin'), function () {
    Route::post('account/{account}/payment/cash/create', ['as' => 'account.payment.cash.create', 'uses' => 'CashPaymentController@store']);
    Route::delete('account/{account}/payment/cash', ['as' => 'account.payment.cash.destroy', 'uses' => 'CashPaymentController@destroy']);
});

Route::post('account/payment/migrate-direct-debit', ['as' => 'account.payment.gocardless-migrate', 'uses' => 'PaymentController@migrateDD', 'middleware' => 'role:member']);


##########################
# Inductions
##########################

Route::group(array('middleware' => 'role:member'), function () {
    Route::post('equipment/{equipment}/induction', ['uses' => 'InductionController@store', 'as' => 'equipment_training.create']);
    Route::post('equipment/{equipment}/induction/{induction}/train', ['uses' => 'InductionController@train', 'as' => 'equipment_training.train']);
    Route::post('equipment/{equipment}/induction/{induction}/untrain', ['uses' => 'InductionController@untrain', 'as' => 'equipment_training.untrain']);
    Route::post('equipment/{equipment}/induction/{induction}/promote', ['uses' => 'InductionController@promote', 'as' => 'equipment_training.promote']);
    Route::post('equipment/{equipment}/induction/{induction}/demote', ['uses' => 'InductionController@demote', 'as' => 'equipment_training.demote']);
    Route::delete('equipment/{equipment}/induction/{induction}', ['uses' => 'InductionController@destroy', 'as' => 'equipment_training.destroy']);
});

##########################
# Equipment
##########################

Route::group(array('middleware' => 'role:member'), function () {
    Route::resource('equipment', 'EquipmentController');
    Route::post('equipment/{equipment}/photo', ['uses' => 'EquipmentController@addPhoto', 'as' => 'equipment.photo.store']);
    Route::delete('equipment/{equipment}/photo/{key}', ['uses' => 'EquipmentController@destroyPhoto', 'as' => 'equipment.photo.destroy']);
});



##########################
# Equipment areas
##########################

Route::resource('equipment_area', 'EquipmentAreaController');

##########################
# Maintainer groups
##########################

Route::resource('maintainer_groups', 'MaintainerGroupController');

##########################
# Induction courses
##########################

Route::group(['middleware' => 'role:member'], function() {
    Route::resource('courses', 'CourseController');

    Route::group(['prefix' => 'courses/{course}', 'as' => 'courses.'], function() {
        Route::post('/request-sign-off', ['uses' => 'CourseInductionController@store', 'as' => 'request-sign-off']);
    });

    Route::group(['prefix' => 'courses/{course}/training', 'as' => 'courses.training.'], function() {
        Route::get('/', ['uses' => 'CourseTrainingController@index', 'as' => 'index']);
        Route::post('/bulk-train', ['uses' => 'CourseTrainingController@bulkTrain', 'as' => 'bulk-train']);
        Route::post('/train/{user}', ['uses' => 'CourseTrainingController@train', 'as' => 'train']);
        Route::post('/untrain/{user}', ['uses' => 'CourseTrainingController@untrain', 'as' => 'untrain']);
        Route::post('/promote/{user}', ['uses' => 'CourseTrainingController@promote', 'as' => 'promote']);
        Route::post('/demote/{user}', ['uses' => 'CourseTrainingController@demote', 'as' => 'demote']);
    });
});

##########################
# Notifications
##########################

Route::resource('notifications', 'NotificationController', ['only' => ['index', 'update'], 'middleware' => 'role:member']);

##########################
# Key fobs
##########################

Route::group(array('middleware' => 'role:member'), function () {
    Route::resource('account/{user}/keyfobs', 'KeyFobController', ['only' => ['index', 'show', 'store', 'update', 'destroy']]);
});


##########################
# Storage Boxes
##########################

// Storage Boxes
// Ordering is important with shared verbs, as the concrete routes must come before catch-alls
Route::get('storage_boxes/create', ['uses' => 'StorageBoxController@create', 'as' => 'storage_boxes.create', 'middleware' => 'role:admin']);
Route::get('storage_boxes', ['uses' => 'StorageBoxController@index', 'as' => 'storage_boxes.index', 'middleware' => 'role:member']);
Route::get('storage_boxes/{storage_box}', ['uses' => 'StorageBoxController@show', 'as' => 'storage_boxes.show', 'middleware' => 'role:member']);
Route::post('storage_boxes', ['uses' => 'StorageBoxController@store', 'as' => 'storage_boxes.store', 'middleware' => 'role:admin']);
Route::post('storage_boxes/{storage_box}/claim', ['uses' => 'StorageBoxClaimController@update', 'as' => 'storage_boxes_claim.update', 'middleware' => 'role:member']);
Route::put('storage_boxes/{storage_box}', ['uses' => 'StorageBoxController@update', 'as' => 'storage_boxes.update', 'middleware' => 'role:admin']);
Route::delete('storage_boxes/{storage_box}/claim', ['uses' => 'StorageBoxClaimController@destroy', 'as' => 'storage_boxes_claim.destroy', 'middleware' => 'role:member']);
Route::delete('storage_boxes/{storage_box}', ['uses' => 'StorageBoxController@destroy', 'as' => 'storage_boxes.destroy', 'middleware' => 'role:admin']);

##########################
# Stats
##########################

Route::get('stats', ['uses' => 'StatsController@index', 'middleware' => 'role:member', 'as' => 'stats.index']);
Route::get('stats/gocardless', ['uses' => 'StatsController@ddSwitch', 'middleware' => 'role:member', 'as' => 'stats.gocardless']);
Route::get('stats/history', ['uses' => 'StatsController@history', 'middleware' => 'role:member', 'as' => 'stats.history']);



##########################
# Notification Emails
##########################

Route::get('notification_email/equipment/{tool_id}/status/{status}', ['as' => 'notificationemail.equipment', 'uses' => 'NotificationEmailController@tool', 'middleware' => 'role:member']);
Route::get('notification_email/create', ['as' => 'notificationemail.create', 'uses' => 'NotificationEmailController@create', 'middleware' => 'role:member']);
Route::post('notification_email', ['as' => 'notificationemail.store', 'uses' => 'NotificationEmailController@store', 'middleware' => 'role:member']);

##########################
# Roles
##########################

Route::group(array('middleware' => 'role:admin'), function () {
    Route::resource('roles', 'RolesController', []);
    Route::resource('roles.users', 'RoleUsersController', ['only' => ['destroy', 'store']]);
});


##########################
# Disciplinary
##########################

Route::group(array('middleware' => 'role:admin'), function () {
    Route::post('disciplinary/{user}/ban', ['uses' => 'DisciplinaryController@ban', 'as' => 'disciplinary.ban']);
    Route::post('disciplinary/{user}/unban', ['uses' => 'DisciplinaryController@unban', 'as' => 'disciplinary.unban']);
});

##########################
# Logviewer
##########################

Route::get('logs', ['uses' => '\Rap2hpoutre\LaravelLogViewer\LogViewerController@index', 'middleware' => 'role:admin'])->name('logs');

##########################
# Email Previews
##########################

Route::group(array('middleware' => 'role:admin'), function () {
    Route::get('email-preview', ['uses' => 'EmailPreviewController@index', 'as' => 'email-preview.index']);
    Route::get('email-preview/payment-warning', ['uses' => 'EmailPreviewController@paymentWarning', 'as' => 'email-preview.payment-warning']);
    Route::get('email-preview/suspended', ['uses' => 'EmailPreviewController@suspended', 'as' => 'email-preview.suspended']);
    Route::get('email-preview/leaving', ['uses' => 'EmailPreviewController@leaving', 'as' => 'email-preview.leaving']);
    Route::get('email-preview/left', ['uses' => 'EmailPreviewController@left', 'as' => 'email-preview.left']);
    Route::get('email-preview/welcome-member', ['uses' => 'EmailPreviewController@welcomeMember', 'as' => 'email-preview.welcome-member']);
    Route::get('email-preview/welcome-member-online-only', ['uses' => 'EmailPreviewController@welcomeMemberOnlineOnly', 'as' => 'email-preview.welcome-member-online-only']);
    Route::get('email-preview/notification', ['uses' => 'EmailPreviewController@notification', 'as' => 'email-preview.notification']);
    Route::get('email-preview/equipment-notification', ['uses' => 'EmailPreviewController@equipmentNotification', 'as' => 'email-preview.equipment-notification']);
    Route::get('email-preview/confirmation', ['uses' => 'EmailPreviewController@confirmation', 'as' => 'email-preview.confirmation']);
    
    // Notification previews
    Route::get('notification-preview/induction-completed', ['uses' => 'NotificationPreviewController@inductionCompleted', 'as' => 'notification-preview.induction-completed']);
    Route::get('notification-preview/induction-marked-as-trainer', ['uses' => 'NotificationPreviewController@inductionMarkedAsTrainer', 'as' => 'notification-preview.induction-marked-as-trainer']);
    Route::get('notification-preview/inductee-induction-requested', ['uses' => 'NotificationPreviewController@inducteeInductionRequested', 'as' => 'notification-preview.inductee-induction-requested']);
    Route::get('notification-preview/trainer-induction-requested', ['uses' => 'NotificationPreviewController@trainerInductionRequested', 'as' => 'notification-preview.trainer-induction-requested']);
    
    // Course-based notification previews
    Route::get('notification-preview/course-induction-completed', ['uses' => 'NotificationPreviewController@courseInductionCompleted', 'as' => 'notification-preview.course-induction-completed']);
    Route::get('notification-preview/course-induction-marked-as-trainer', ['uses' => 'NotificationPreviewController@courseInductionMarkedAsTrainer', 'as' => 'notification-preview.course-induction-marked-as-trainer']);
    Route::get('notification-preview/course-inductee-induction-requested', ['uses' => 'NotificationPreviewController@courseInducteeInductionRequested', 'as' => 'notification-preview.course-inductee-induction-requested']);
    Route::get('notification-preview/course-trainer-induction-requested', ['uses' => 'NotificationPreviewController@courseTrainerInductionRequested', 'as' => 'notification-preview.course-trainer-induction-requested']);
});
