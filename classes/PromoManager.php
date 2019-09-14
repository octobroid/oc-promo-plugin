<?php namespace Octobro\Promo\Classes;

use Carbon\Carbon;

class PromoManager
{
	use \October\Rain\Support\Traits\Singleton;

	public $rules = [];

	public $outputs = [];

	public function __construct()
	{
		$this->addRuleFields();
	}
	
	public function init()
	{
	}
	
	/**
	 * Undocumented function
	 *
	 * @param [type] $form
	 * @return void
	 */
	public function addRuleFields()
    {
		$self = $this;
		
		\Octobro\Promo\Controllers\Promos::extendFormFields(function ($form, $model, $context) use ($self) {
			// if (!$model instanceof \Octobro\Promo\Models\Rule) return;
			
			if (! isset($form->getField('rules')->config)) return;

			$rulesFieldConfig = $form->getField('rules')->config;
			
			foreach ($self->listRules() as $class => $rule) {

				$ruleObject = new $class;

				$properties = $ruleObject->registerProperties();

				foreach ($properties as $key => $ruleProperty) {

					$ruleProperty['span'] = array_get($ruleProperty, 'span', 'auto');
					$ruleProperty['trigger'] = [
						'action'    => 'show',
						'field'     => 'rule_code',
						'condition' => 'value[' . array_get($rule, 'code') . ']',
					];

					$rulesFieldConfig['form']['fields'][array_get($rule, 'code') . '['.$key.']'] = $ruleProperty;

					// $form->addFields([
					// 	'options[' . array_get($rule, 'code') . '][' . $key . ']' => $ruleProperty,
					// ]);
				}
			}
			
			$form->addTabFields([
				'rules' => $rulesFieldConfig
			]);
		});

		\Octobro\Promo\Controllers\Promos::extendFormFields(function ($form, $model, $context) use ($self) {
			if (! isset($form->getField('outputs')->config)) return;

			$outputsFieldConfig = $form->getField('outputs')->config;

			foreach ($self->listOutputs() as $class => $output) {
				
				$outputObject = new $class;
				
				$properties = $outputObject->registerProperties();

				foreach ($properties as $key => $outputProperty) {
					
					$outputProperty['span'] = array_get($outputProperty, 'span', 'auto');
					$outputProperty['trigger'] = [
						'action'    => 'show',
						'field'     => 'output_code',
						'condition' => 'value[' . array_get($output, 'code') . ']',
					];

					$outputsFieldConfig['form']['fields'][array_get($output, 'code') . '['.$key.']'] = $outputProperty;
					
				}

				// dd($outputsFieldConfig);
				$form->addTabFields([
					'outputs' => $outputsFieldConfig
				]);
			}
			// dd('s');

			// $form->addTabFields([
            //     'outputs' => array_merge($form->getField('outputs')->config, [
            //         'options' => $this->getRuleLists($rules)
            //     ])
            // ]);
		});
	}

	/**
	 * [registerRule description]
	 * @param  [type] $className [description]
	 * @param  [type] $ruleInfo  [description]
	 * @return [type]            [description]
	 */
	public function registerRule($className)
    {
		if (!class_exists($className)) {
			throw new \ApplicationException('Class ' . $className . ' does not exist.');
		}

    	$ruleObject = new $className;

		$ruleCode = array_get($ruleObject->ruleDetails(), 'code');
		
		if (!$ruleCode) {
			throw new \ApplicationException('Please specify the rule code for registration.');
		}

		$this->rules[$className] = $ruleObject->ruleDetails();
    }

    /**
     * [registerRules description]
     * @param  [type] $rules [description]
     * @return [type]        [description]
     */
    public function registerRules($rules)
    {
    	foreach ($rules as $className) {
    		$this->registerRule($className);
		}
    }

	/**
	 * Undocumented function
	 *
	 * @param [type] $code
	 * @return void
	 */
    public function findRuleByCode($code)
    {
    	foreach ($this->rules as $className => $rule) {
    		if (array_get($rule, 'code') == $code) {
    			return new $className;
    		}
    	}
	}

	public function listRules()
	{
		return $this->rules;
	}
	
	public function registerOutput($className)
    {
		if (!class_exists($className)) {
			throw new \ApplicationException('Class ' . $className . ' does not exist.');
		}

    	$outputObject = new $className;

		$outputCode = array_get($outputObject->outputDetails(), 'code');
		
		if (!$outputCode) {
			throw new \ApplicationException('Please specify the output code for registration.');
		}

		$this->outputs[$className] = $outputObject->outputDetails();
    }

    public function registerOutputs($outputs)
    {
    	foreach ($outputs as $className) {
    		$this->registerOutput($className);
    	}
	}
	
	public function listOutputs()
	{
		return $this->outputs;
	}

	public function findOutputByCode($code)
    {
    	foreach ($this->outputs as $className => $output) {
    		if (array_get($output, 'code') == $code) {
    			return new $className;
    		}
    	}
	}

	public function applyOutputs($redemption, $context)
	{
		if (!isset($redemption->coupon) || !isset($redemption->coupon->promo) || !$redemption->coupon->promo) return;
		
		$promo = $redemption->coupon->promo;
		
		foreach ($promo->outputs as $output) {
			$outputObject = $this->findOutputByCode(array_get($output, 'output_code'));

			switch ($context) {
				case 'hold':
					$outputObject->onHold($redemption, array_get($output, array_get($output, 'output_code')));
					break;
				case 'redeem':
					$outputObject->onRedeem($redemption, array_get($output, array_get($output, 'output_code')));
					break;
				case 'release':
					$outputObject->onRelease($redemption, array_get($output, array_get($output, 'output_code')));
					break;
			}
		}

	}

}