<?php namespace Octobro\Promo\Classes;

use Auth;
use Carbon\Carbon;
use Octobro\Promo\Models\Coupon;
use Octobro\Promo\Models\CouponRedemption;

class Validator
{
    use \October\Rain\Support\Traits\Singleton;

    public $promoManager;
    public $error_message;
    public $outputType;
    public $output;

    public $isSimulation = false;

    public function __construct()
    {
        $this->promoManager = PromoManager::instance();
    }

    /**
     * Validate coupon
     * @param  string $code    Coupon code input
     * @param  array  $options Options
     * @param  array  $target Target
     * @param  integer  $count Count of couopn
     * @return [type]          [description]
     */
    public function validate($code, $options = [], $target = [], $count = 1, $user = null)
    {
        $coupon = Coupon::whereCode($code)->first();

        Event::fire('octobro.promo.beforeValidate', [&$code, &$options, &$target, &$count, &$user]);

        // If not found
        if (!$coupon) {
            $this->error_message = 'Coupon not found.';
            return false;
        }

        // If no stock anymore
        if (!is_null($coupon->stock) && $coupon->stock < $count) {
            $this->error_message = $coupon->stock > 0 ? sprintf('Only %s coupon(s) available.', $coupon->stock) : 'No more coupon.';
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

        if (!$this->validateRule($coupon, $options, $target)) {
            return false;
        }

        // If valid example
        $output                 = [];
        $output['promo']        = $coupon->promo->name;
        $output['coupon_id']    = $coupon->id;
        $output['coupon_code']  = $coupon->code;
        $output['coupon_stock'] = $coupon->stock;
        $output['message']      = $coupon->promo->success_message ?: 'Your coupon is valid!';
        $output['target']       = [];

        // If has output type, select only the selected type
        if ($this->outputType) {
            $promoOutputs = array_filter($coupon->promo->output, function($item) {
                return $item['type'] == $this->outputType;
            });
        } else {
            // If no, get the null type
            $promoOutputs = array_filter($coupon->promo->output, function($item) {
                return $item['type'] == null;
            });
        }

        if (!$promoOutputs) {
            return false;
        }

        foreach ($promoOutputs as $promoOutput) {
            $targetAmount = isset($target[$promoOutput['target']]) ? $target[$promoOutput['target']] : 0;
            $amount       = 0;

            // get the amount
            $amount = $this->fixOrPercentage($promoOutput['output_amount'], $promoOutput['output_type'], $targetAmount);

            // limit the max
            if($promoOutput['output_max_amount']) {
                $amount = min($amount, $this->fixOrPercentage($promoOutput['output_max_amount'], $promoOutput['output_max_type'], $targetAmount));
            }

            // limit the min
            if($promoOutput['output_min_amount']) {
                $amount = max($amount, $this->fixOrPercentage($promoOutput['output_min_amount'], $promoOutput['output_min_type'], $targetAmount));
            }

            $output['target'][$promoOutput['target']] = (int) min($amount, $targetAmount);
        }

        $this->output = $output;

        $coupon->hold($user ?: Auth::getUser());

        return true;
    }

    protected function fixOrPercentage($amount, $type, $targetAmount)
    {
        if ($type == 'percentage') {
            return (int) $amount / 100 * (int) $targetAmount;
        }

        return (int) $amount;
    }

    /**
     * [validateRule description]
     * @param  [type] $coupon  [description]
     * @param  [type] $options [description]
     * @return [type]          [description]
     */
    public function validateRule($coupon, $options, $target)
    {
        $errorMessage = null;

        $rules = $coupon->promo->promo_rules()->orderBy('sort_order', 'asc')->get();

        $result = true;
        $outputType = null;

        foreach ($rules as $rule) {
            $ruleObject = $this->promoManager->findRuleByCode($rule->type);

            if (!$ruleObject) {
                return false;
            }

            $ruleObject->props = isset($rule->options[$rule->type]) ? $rule->options[$rule->type] : [];

            $value = $ruleObject->validate($options, $target);

            switch($rule->operator) {
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
                if(! $errorMessage) {

                    $errorMessage = $rule->error_message ?: $ruleObject->error_message;
                }
            } elseif($value && $rule->output_type) {
            	$outputType = $rule->output_type;
            }
        }

        if ($result) {
        	$this->outputType = $outputType;
        } else {
        	$this->error_message = $errorMessage;
        }

        return $result;
    }

}
