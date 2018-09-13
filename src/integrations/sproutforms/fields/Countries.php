<?php

namespace barrelstrength\sproutformscountries\integrations\sproutforms\fields;

use Craft;
use craft\helpers\Template as TemplateHelper;
use craft\base\ElementInterface;
use craft\base\PreviewableFieldInterface;
use barrelstrength\sproutbase\app\fields\helpers\AddressHelper;
use barrelstrength\sproutforms\base\FormField;

/**
 * Class Countries
 *
 * @package Craft
 */
class Countries extends FormField implements PreviewableFieldInterface
{
    /**
     * @var string
     */
    public $cssClasses;

    /**
     * @var string|null Default Country
     */
    public $defaultCountry;

    /**
     * @var mixed All Countries
     */
    public $options;

    /**
     * @var array Common countries
     */
    public $commonCountries;

    public function init()
    {
        if (is_null($this->options)){
            $this->options = $this->getOptions();
        }

        parent::init();
    }

    /**
     * @inheritdoc
     */
    public static function displayName(): string
    {
        return Craft::t('sprout-forms-countries', 'Countries');
    }

    /**
     * @return string
     */
    public function getSvgIconPath()
    {
        return '@sproutformscountriesicons/globe.svg';
    }

    /**
     * @inheritdoc
     *
     * @throws \Twig_Error_Loader
     * @throws \yii\base\Exception
     */
    public function getSettingsHtml()
    {
        $options = $this->getOptions();

        $rendered = Craft::$app->getView()->renderTemplate(
            'sprout-forms-countries/_integrations/sproutforms/formtemplates/fields/countries/settings',
            [
                'field' => $this,
                'options' => $options
            ]
        );

        return $rendered;
    }

    /**
     * @inheritdoc
     *
     * @throws \Twig_Error_Loader
     * @throws \yii\base\Exception
     */
    public function getInputHtml($value, ElementInterface $element = null): string
    {
        return Craft::$app->getView()->renderTemplate('_includes/forms/select',
            [
                'name' => $this->handle,
                'value' => $value ?? $this->defaultCountry,
                'options' => $this->options
            ]
        );
    }

    /**
     * @inheritdoc
     *
     * @throws \Twig_Error_Loader
     * @throws \yii\base\Exception
     */
    public function getExampleInputHtml()
    {
        return Craft::$app->getView()->renderTemplate('sprout-forms-countries/_integrations/sproutforms/formtemplates/fields/countries/example',
            [
                'field' => $this,
                'options' => $this->options
            ]
        );
    }

    /**
     * @inheritdoc
     *
     * @throws \Twig_Error_Loader
     * @throws \yii\base\Exception
     */
    public function getFrontEndInputHtml($value, array $renderingOptions = null): string
    {
        $rendered = Craft::$app->getView()->renderTemplate(
            'countries/input',
            [
                'name' => $this->handle,
                'value' => $value ?? $this->defaultCountry,
                'field' => $this,
                'options' => $this->options,
                'renderingOptions' => $renderingOptions
            ]
        );

        return TemplateHelper::raw($rendered);
    }

    /**
     * @inheritdoc
     */
    public function getTemplatesPath()
    {
        return Craft::getAlias('@barrelstrength/sproutformscountries/templates/_integrations/sproutforms/formtemplates/fields/');
    } 

    /**
     * Return Countries as options for select field
     *
     * @return array
     */
    private function getOptions()
    {
        $addressHelper = new AddressHelper();

        $countries = $addressHelper->getCountries();

        return $countries;
    }
}
