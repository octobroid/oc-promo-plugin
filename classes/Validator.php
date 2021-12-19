<?php namespace Octobro\Promo\Classes;

use Auth;
use Event;
use Promo;
use ApplicationException;
use Carbon\Carbon;
use Octobro\Promo\Models\Coupon;
use Octobro\Promo\Models\CouponRedemption;

class Validator
{
    use \October\Rain\Support\Traits\Singleton;

    public $error_message;
    public $outputType;
    public $output;

    public $isSimulation = false;

    /**
     * Validate coupon
     * @param  string $code    Coupon code input
     * @param  array  $options Options
     * @param  integer  $count Count of couopn
     * @return [type]          [description]
     */
    public function validate($code, $options = [], $count = 1, $user = null)
    {
        $coupon = Coupon::whereNotNull('promo_id')->whereCode($code)->first();

        // If not found
        if (!$coupon) {
            $this->error_message = 'Coupon not found.';
            return false;
        }
        
        Event::fire('octobro.promo.beforeValidate', [&$coupon, &$options, &$count, &$user]);

        // If no stock anymore
        if (!is_null($coupon->stock) && $coupon->stock_used + $count > $coupon->stock) {
            $this->error_message = $coupon->stock - $coupon->stock_used > 0 ? sprintf('Only %s coupon(s) available.', $coupon->stock - $coupon->stock_used) : 'No more coupon.';
            return false;
        }

        // Check based on promotion period, except it's a simulationns
        if ($coupon->promo && !$this->isSimulation) {
            // If promo inactive
            if (!$coupon->promo->is_active) {
                $this->error_message = 'Promotion is no longer active.';
                return false;
            }

            if ($coupon->promo->start_at && Carbon::now() < $coupon->promo->start_at) {
                $this->error_message = 'Promotion is not yet started.';
                return false;
            }

            if ($coupon->promo->end_at && Carbon::now() > $coupon->promo->end_at) {
                $this->error_message = 'Promotion is finished.';
                return false;
            }
        }
        
        if (!$this->validateRule($coupon, $options)) {
            return false;
        }

        $this->success_message = $coupon->promo->success_message ?: 'Your coupon is valid!';

        if (!$this->isSimulation) {
            $coupon->hold($user ?: Auth::getUser(), $count, $options, $coupon->promo->outputs);
        }

        return true;
    }

    /**
     * [validateRule description]
     * @param  [type] $coupon  [description]
     * @param  [type] $options [description]
     * @return [type]          [description]
     */
    public function validateRule($coupon, $options)
    {
        $result = true;

        $promo = $coupon->promo;

        if (!$promo) {
            throw new ApplicationException('Promo not found.');
        }

        foreach ($promo->rules as $rule) {
            $code = array_get($rule, 'rule_code');

            $ruleObject = Promo::findRuleByCode($code);
            
            if (!$ruleObject) {
                return false;
            }

            $ruleObject->props = array_get($rule, $code, []);

            $value = $ruleObject->validate($options);

            switch (array_get($rule, 'operator', 'and')) {
            	case 'or':
            		$result = $result || $value;
            		break;
            	case 'and':
            		$result = $result && $value;
            		break;
            	case 'xor':
            		$result = $result xor $value;
            		break;
            }

            if ($result === false) {
                $this->error_message = $ruleObject->error_message;
            }
        }

        return $result;
    }

}
