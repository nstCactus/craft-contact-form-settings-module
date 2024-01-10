<?php

namespace nstcactus\craftcms\modules\contactFormSettings;

use Craft;
use craft\web\Application;
use nstcactus\craftcms\modules\contactFormSettings\web\twig\Extension;
use nstcactus\CraftUtils\AbstractModule;
use yii\base\InvalidConfigException;

class ContactFormSettingsModule extends AbstractModule
{
    public const FORM_NAME_FIELD = 'message.formName';

    public function init(): void
    {
        parent::init();

        $request = Craft::$app->getRequest();
        if (!$request->getIsConsoleRequest() && $request->getIsSiteRequest()) {
            Craft::$app->on(
                Application::EVENT_INIT,
                function () {
                    $request = Craft::$app->getRequest();

                    if ($request->getBodyParam(self::FORM_NAME_FIELD) === null) {
                        return;
                    }

                    $formName = $request->getValidatedBodyParam(self::FORM_NAME_FIELD);
                    try {
                        $this->get($formName);
                    } catch(InvalidConfigException $e) {
                        throw new InvalidConfigException(
                            "No such service defined in the ContactFormSettingsModule: $formName",
                            $e->getCode(),
                            $e
                        );
                    }
                }
            );
        }
    }

    protected function getTwigExtensions(): ?array
    {
        return [
            new Extension(),
        ];
    }
}
