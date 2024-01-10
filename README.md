# ContactFormSettings module for Craft CMS 3.x

This module helps to manage multiple sets of settings form the Craft `contact-form` & `contact-form-extensions` plugins.


## Requirements

This module requires:

- PHP 7.1 or later
- Craft CMS 3.4 or later
- contact-form plugin 2.2 or later
- contact-form-extensions plugin 1.2.1 or later


## Installation

To install the module, follow these instructions.

First, you'll need to add the contents of the `app.php` file to your `config/app.php` (or just copy it there if it does
not exist). This ensures that your module will get loaded for each request.

The file might look something like this:

```php
return [
    'modules' => [
        'contact-form-settings-module' => [
            'class' => \nstcactus\craftcms\modules\contactFormSettings\ContactFormSettingsModule::class,
            'components' => [
                // More on this below
            ],
        ],
    ],
    'bootstrap' => ['contact-form-settings-module'],
];
```

## Add a form

1. Create a form class that extends `\modules\contactFormSettings\forms\AbstractContactForm`.
This class will describe the settings of your form.

2. Register this form class in the `config/app.php` file: add an entry in the `components` array of the module where the
   key is the form name and the value is a reference to the form class.

   Example:

    ```php
    return [
        'modules' => [
            'contact-form-settings-module' => [
                'class' => \nstcactus\craftcms\modules\contactFormSettings\ContactFormSettingsModule::class,
                'components' => [
                    'contact' => \modules\app\forms\ContactForm::class,
                ],
            ],
        ],
        'bootstrap' => ['contact-form-settings-module'],
    ];
    ```

3. Add the following in the template of the form, inside the `<form>` element: `{{ formNameInput('contact') }}`


## Form settings

### Plugin settings override

Each form setting class must implement the `getContactFormConfiguration()` & `getContactFormExtensionsConfiguration()`
methods. They expect a return value that is a settings array, just like what would be set in the
`config/contact-form.php` & `contact-form-extensions.php`.

### Custom validation

Custom validation rules should be defined by overriding the `afterValidateSubmission()` method. Here you can add
validation errors on the `Submission` instance (available in `$e->sender`) like so:

````php
public function afterValidateSubmission(Event $e): void
{
    /** @var Submission $submission */
    $submission = $e->sender;

    if (empty($submission->message['FirstName'])) {
        $submission->addError('message.FirstName', Craft::t('site', 'This field cannot be blank.'));
    }
}
````

### FAQ

#### How do I safely let the use pick from a list of subjects?

When the subject is selected by the user in a `<select>` element for example, the recommended approach is

#### How do I set the recipient dynamically?

To set the recipient dynamically, you can either:

- set it in the `toEmail` property in the array return from the `getContactFormSetting()` method
- set it using the [`contact-form-extensions` override mechanism](https://github.com/hybridinteractive/craft-contact-form-extensions?tab=readme-ov-file#overriding-where-the-message-is-sent)
  in either the `beforeValidateSubmission()` or the `afterValidateSubmission()` method.
  Example:

  ````php
  public function beforeValidateSubmission(ModelEvent $e): void
  {
    parent::beforeValidateSubmission($e);
    $submission = $e->sender->message['toEmail'] = Craft::$app->getSecurity()->hashData($subject->contactSubjectRecipient);
  }
  ````

#### How do I use separate fields for first name & last name?

When using separate first & last name field as opposed to a single `fromName` field, make sure to actually set the
`fromName` property of the `Submission` instance in the `afterValidateSubmission()` method.

This will improve submission index in the control panel.

Example:

````php
public function afterValidateSubmission(Event $e): void
{
    /** @var Submission $submission */
    $submission = $e->sender;

    if (!empty($submission->message['FirstName']) || empty($submission->message['LastName'])) {
        $submission->fromName = trim(sprintf(
            '%s %s',
            $submission->message['FirstName'] ?? '',
            $submission->message['LastName'] ?? ''
        ));
    }
}
````
