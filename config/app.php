<?php

use \nstcactus\craftcms\modules\contactFormSettings\ContactFormSettingsModule;

return [
    'modules' => [
        'contact-form-settings-config' => [
            'class' => ContactFormSettingsModule::class,
            'components' => [],
        ],
    ],
    'bootstrap' => ['contact-form-settings-config'],
];
