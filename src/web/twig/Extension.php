<?php

namespace nstcactus\craftcms\modules\contactFormSettings\web\twig;

use Craft;
use craft\helpers\Html;
use Twig\Extension\AbstractExtension;
use Twig\Markup;
use Twig\TwigFunction;

class Extension extends AbstractExtension
{
    public function getFunctions(): array
    {
        return [
            new TwigFunction('formNameInput', function ($name) {
                return new Markup(Html::hiddenInput('message[formName]', Craft::$app->getSecurity()->hashData($name)), 'utf-8');
            }),
        ];
    }
}
