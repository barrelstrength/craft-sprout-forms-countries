<?php

namespace barrelstrength\sproutformscountries\integrations\sproutforms\fields;

use barrelstrength\sproutbasefields\services\Address;
use barrelstrength\sproutforms\elements\Entry;
use CommerceGuys\Addressing\Country\CountryRepository;
use craft\fields\PlainText as CraftPlainText;
use Craft;
use craft\helpers\Template as TemplateHelper;
use craft\base\ElementInterface;
use craft\base\PreviewableFieldInterface;
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
        if (is_null($this->options)) {
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
    public function getSvgIconPath(): string
    {
        $icons = [
            '@sproutformscountriesicons/globe-africa.svg',
            '@sproutformscountriesicons/globe-americas.svg',
            '@sproutformscountriesicons/globe-asia.svg',
        ];

        return $icons[array_rand($icons)];
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

        $commonCountries = $this->commonCountries;

        if (count($options) && is_array($commonCountries) && count($commonCountries)) {
            foreach ($options as $key => $label) {
                if (in_array($key, $commonCountries)) {
                    $this->moveToTop($options, $key);
                }
            }
        }

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
    public function getExampleInputHtml(): string
    {
        $options = ['' => Craft::t('sprout-forms-countries', 'Select...')] + $this->options;

        return Craft::$app->getView()->renderTemplate(
            'sprout-forms-countries/_integrations/sproutforms/formtemplates/fields/countries/example',
            [
                'field' => $this,
                'options' => $options
            ]
        );
    }

    /**
     * @inheritdoc
     *
     * @throws \Twig_Error_Loader
     * @throws \yii\base\Exception
     */
    public function getFrontEndInputHtml($value, Entry $entry, array $renderingOptions = null): \Twig_Markup
    {
        $commonCountries = $this->getCommonCountries();

        $selectOption = ['' => Craft::t('sprout-forms-countries', 'Select...')];

        // Add a Select... option to the beginning of the list
        if ($commonCountries) {
            $commonCountries = $selectOption + $commonCountries;
        } else {
            $this->options = $selectOption + $this->options;
        }

        // Add a spacer to the end of the list
        // @todo - make sure we validate the submission to match a country so this can't be selected
        if ($commonCountries) {
            $commonCountries['--'] = '--';
        }

        $rendered = Craft::$app->getView()->renderTemplate(
            'countries/input',
            [
                'name' => $this->handle,
                'value' => $value ?? $this->defaultCountry,
                'field' => $this,
                'entry' => $entry,
                'commonCountries' => $commonCountries,
                'options' => $this->options,
                'renderingOptions' => $renderingOptions
            ]
        );

        return TemplateHelper::raw($rendered);
    }

    /**
     * @inheritdoc
     */
    public function getTemplatesPath(): string
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
        $countryRepository = new CountryRepository();

        $countries = $countryRepository->getList(Address::DEFAULT_LANGUAGE);

        return $countries;
    }

    /**
     * Format common countries setting values with country names
     *
     * @return array
     */
    private function getCommonCountries()
    {
        $countryRepository = new CountryRepository();
        $options = [];

        $commonCountries = $this->commonCountries;

        if (is_array($commonCountries) && count($commonCountries)) {
            foreach ($commonCountries as $code) {
                $options[$code] = $countryRepository->get($code)->getName();
            }
        }

        return $options;
    }

    /**
     * @inheritdoc
     */
    public function getCompatibleCraftFieldTypes(): array
    {
        return [
            CraftPlainText::class
        ];
    }

    /**
     * Move selected countries to the top of the dropdown
     *
     * @param $array
     * @param $key
     */
    private function moveToTop(&$array, $key)
    {
        $temp = [$key => $array[$key]];
        unset($array[$key]);
        $array = $temp + $array;
    }
}
