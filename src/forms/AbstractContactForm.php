<?php

namespace nstcactus\craftcms\modules\contactFormSettings\forms;

use Craft;
use craft\contactform\models\Submission;
use craft\contactform\Plugin as ContactFormPlugin;
use craft\errors\MissingComponentException;
use hybridinteractive\contactformextensions\ContactFormExtensions as ContactFormExtensionsPlugin;
use nstcactus\craftcms\modules\contactFormSettings\ContactFormSettingsModule;
use yii\base\Component;
use yii\base\Event;
use yii\base\ModelEvent;

abstract class AbstractContactForm extends Component
{
    public function init(): void
    {
        parent::init();

        Event::on(ContactFormPlugin::class, ContactFormPlugin::EVENT_BEFORE_ACTION, [ $this, 'overrideContactFormPluginConfiguration']);
        Event::on(ContactFormPlugin::class, ContactFormPlugin::EVENT_BEFORE_ACTION, [ $this, 'overrideContactFormExtensionsPluginConfiguration']);
        Event::on(Submission::class, Submission::EVENT_BEFORE_VALIDATE, [ $this, 'beforeValidateSubmission' ], null, false);
        Event::on(Submission::class, Submission::EVENT_AFTER_VALIDATE, [ $this, 'afterValidateSubmission' ]);
        Event::on(Submission::class, Submission::EVENT_AFTER_VALIDATE, static function (Event $e) {
            $submission = $e->sender;
            $submission->message['formName'] = Craft::$app->getRequest()->getValidatedBodyParam(ContactFormSettingsModule::FORM_NAME_FIELD);
        });

        Craft::info(sprintf('%s contact form customization class loaded.', get_class($this)), __METHOD__);
    }

    /**
     * Override the contact-form plugin configuration with a form-specific one.
     * The new config is the return value of $this->getContactFormConfiguration().
     * @throws MissingComponentException If the contact-form plugin isn't loaded
     */
    public function overrideContactFormPluginConfiguration(): void
    {
        $contactFormPlugin = ContactFormPlugin::getInstance();
        if (!$contactFormPlugin) {
            throw new MissingComponentException('The required contact-form plugin isn\'t loaded.');
        }

        $settings = $contactFormPlugin->getSettings()->toArray();

        $contactFormPlugin->setSettings($this->getContactFormConfiguration($settings));
    }

    /**
     * Override the contact-form-extensions plugin configuration with a form-specific one.
     * The new config is the return value of $this->getContactFormExtensionsConfiguration().
     * @throws MissingComponentException If the contact-form-extensions plugin isn't loaded
     */
    public function overrideContactFormExtensionsPluginConfiguration(): void
    {
        $contactFormExtensionsPlugin = ContactFormExtensionsPlugin::getInstance();
        if (!$contactFormExtensionsPlugin) {
            throw new MissingComponentException('The required contact-form-extensions plugin isn\'t loaded.');
        }

        $settings = $contactFormExtensionsPlugin->getSettings()->toArray();

        $contactFormExtensionsPlugin->setSettings($this->getContactFormExtensionsConfiguration($settings));
    }

    /**
     * Submission::EVENT_BEFORE_VALIDATION event handler. Useful to edit submitted data before validation (eg. adding an
     * attribute not present in the form but required by the contact-form plugin).
     * @param ModelEvent $e
     */
    public function beforeValidateSubmission(ModelEvent $e): void
    {
    }

    /**
     * Submission::EVENT_AFTER_VALIDATION event handler. Useful to add custom validation logic.
     * @param ModelEvent $e
     */
    public function afterValidateSubmission(Event $e): void
    {}

    /**
     * Return a form-specific configuration for the contact-form plugin.
     * @param array $currentSettings The current configuration of the contact-form plugin
     * @return array
     */
    abstract protected function getContactFormConfiguration(array $currentSettings): array;

    /**
     * Return a form-specific configuration for the contact-form-extensions plugin.
     * @param array $currentSettings The current configuration of the contact-form-extensions plugin
     * @return array
     */
    abstract protected function getContactFormExtensionsConfiguration(array $currentSettings): array;
}
