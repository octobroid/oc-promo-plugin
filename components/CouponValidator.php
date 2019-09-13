<?php namespace Octobro\Promo\Components;

use Flash;
use Cms\Classes\ComponentBase;
use Octobro\Promo\Classes\Validator;

class CouponValidator extends ComponentBase
{

    public function componentDetails()
    {
        return [
            'name'        => 'couponValidator Component',
            'description' => 'No description provided yet...'
        ];
    }

    public function defineProperties()
    {
        return [
            'input_name' => [
                'title'       => 'Input Name',
                'description' => 'Input name for code at your form.',
                'default'     => 'code',
                'type'        => 'string'
            ],
            'input_code_placeholder' => [
                'title'       => 'Input Code Placeholder',
                'description' => 'Placeholder text inside the coupon code input.',
                'default'     => 'Type your coupon code...',
                'type'        => 'string'
            ],
            'show_button' => [
                'title' => 'Show Button',
                'description' => 'Trigger the using the button.',
                'type' => 'switch',
                'default' => true,
            ],
        ];
    }

    public function onRun()
    {

    }

    public function onCheck()
    {
        $validator = Validator::instance();
        
        // Get input code
        $code = trim(post($this->property('input_name')));
        
        if (!$code) {
            throw new \ApplicationException('Please fill the code.');
        }

        // Validate
        if ($validator->validate($code, post('options'), post('target'))) {            
            // return success message
            Flash::success($validator->output['message']);
        } else {
            // return error message
            Flash::error($validator->error_message);
        }
    }

}