<?php namespace Octobro\Promo\Classes;

use Validator as LaravelValidator;

/**
 * This is a base class for rule
 */
abstract class RuleBase
{

    public $props = [];

    public $options = [];

	public $error_message = 'Invalid coupon.';

    public function ruleDetails()
    {
        return [
            'code'        => '',
            'name'        => 'Unknown',
            'description' => 'Unknown rule.',
        ];
    }

    public function validate($options = [])
    {
        if (!$this->validateOptions($options)) {
            return false;
        }

        return $this->onValidate($options);
    }

    public function registerProperties()
    {
        return [];
    }

    public function onValidate($options)
    {
        return true;
    }

    public function property($key)
    {
        return array_get($this->props, $key);
    }

    public function validateOptions($options)
    {
        $validator = LaravelValidator::make(
            $options,
            $this->options
        );

        if ($validator->fails()) {
            $this->error_message = 'Invalid option parameters: "' . $validator->messages()->first() . '".';
            return false;
        }

        return true;
    }

}