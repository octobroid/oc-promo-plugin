<?php namespace Octobro\Promo\Classes;

use Carbon\Carbon;

class PromoManager
{
	use \October\Rain\Support\Traits\Singleton;

	public $rules = [];

	public function __construct()
	{
		//
	}

	public function init()
	{
		//
	}

	/**
	 * [registerRule description]
	 * @param  [type] $className [description]
	 * @param  [type] $ruleInfo  [description]
	 * @return [type]            [description]
	 */
	public function registerRule($className)
    {
    	$ruleObject = new $className;

        $ruleCode = isset($ruleObject->details['code']) ? $ruleObject->details['code'] : null;

        $this->rules[$className] = $ruleObject->details;
    }

    /**
     * [registerRules description]
     * @param  [type] $array [description]
     * @return [type]        [description]
     */
    public function registerRules($array)
    {
    	foreach($array as $className) {
    		$this->registerRule($className);
    	}
    }

    public function addRuleFields($form)
    {
    	foreach($this->rules as $class => $rule) {

    		$ruleObject = new $class;

            $properties = $ruleObject->registerProperties() ?: [];

    		foreach($properties as $key => $ruleProperty) {

	    		$ruleProperty['span'] = isset($ruleProperty['span']) ? $ruleProperty['span'] :'auto';
	    		$ruleProperty['trigger'] = [
		            'action' => 'show',
		            'field' => 'type',
		            'condition' => 'value[' . $rule['code'] . ']',
	    		];

    			$form->addFields([
		            'options[' . $rule['code'] . '][' . $key . ']' => $ruleProperty,
		        ]);
    		}
    	}
    }

    public function findRuleByCode($code)
    {
    	foreach($this->rules as $className => $rule) {
    		if($rule['code'] == $code) {
    			return new $className;
    		}
    	}
    }

}