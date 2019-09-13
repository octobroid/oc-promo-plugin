<?php namespace Octobro\Promo\Classes;

use Validator as LaravelValidator;

/**
 * This is a base class for rule
 */
abstract class RuleBase
{

    public $props = [];

    public $options = [];

    public $details = [];

	public $error_message = 'Invalid coupon.';

    public function __construct()
    {
        $this->details = $this->ruleDetails();
    }

    public function validate($options = [], $target = [])
    {
        $options = is_array($options) ? $options : [];

        if (!$this->validateOptions($options)) {
            return false;
        }

        return $this->onValidate($options, $target);
    }

    public function ruleDetails()
    {
    }

    public function registerProperties()
    {
    }

    public function onValidate($options, $target)
    {
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