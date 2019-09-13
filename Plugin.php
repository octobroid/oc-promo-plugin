<?php namespace Octobro\Promo;

use Event;
use System\Classes\PluginBase;
use Octobro\Promo\Models\Coupon;
use Octobro\Promo\Classes\PromoManager;
use Octobro\Promo\Classes\Validator;

class Plugin extends PluginBase
{
    public $require = ['RainLab.User'];

    public function registerComponents()
    {
    	return [
    		'Octobro\Promo\Components\CouponValidator' => 'couponValidator',
            'Octobro\Promo\Components\PromoDetail'     => 'promoDetail',
    	];
    }

    public function boot()
    {
    	$promoManager = PromoManager::instance();

    	//
    	// Built in Validators
    	//

    	$promoManager->registerRules([
            // 'Octobro\Promo\Rules\Products',
            // 'Octobro\Promo\Rules\Brands',
            // 'Octobro\Promo\Rules\Subtotal',
        ]);

        \Octobro\Promo\Controllers\Promos::extendFormFields(function($form, $model, $context) use($promoManager) {
            if (!$model instanceof \Octobro\Promo\Models\Rule)
                return;

            $promoManager->addRuleFields($form);

        });

        // Event::listen('order.afterCreate', function($order, $data) {
        //     $code = isset($data['code']) ? trim($data['code']) : '';

        //     if (!$code)
        //         return;

        //     $validator = Validator::instance();

        //     $options = [
        //         'products' => $order->products,
        //     ];

        //     $target = [
        //         'subtotal' => $order->subtotal,
        //     ];

        //     $count = 1;

        //     // Validate
        //     if($validator->validate($code, $options, $target, $count)) {

        //         // Get the coupon
        //         $coupon = Coupon::find($validator->output['coupon_id']);

        //         // Hold the coupon based on determined amount
        //         $redemption = $coupon->hold($order->user_id, $count);

        //         $order->coupon_code = $coupon->code;
        //         $order->coupon_redemption_id = $redemption->id;

        //         if (isset($validator->output['target']['subtotal'])) {
        //             $order->discount = $validator->output['target']['subtotal'];
        //         }

        //         $order->save();

        //     }
        // });

        // Extend Order backend list
        // Event::listen('backend.list.extendColumns', function($widget) {

        //     // Only for the Order controller
        //     if (!$widget->getController() instanceof \Octobro\Octobro\Controllers\Orders) {
        //         return;
        //     }

        //     // Only for the Order model
        //     if (!$widget->model instanceof \Octobro\Octobro\Models\Order) {
        //         return;
        //     }

        //     $widget->addColumns(
        //         [
        //             'coupon_code' => [
        //                 'label'      => 'Promo Code',
        //                 'type'       => 'text',
        //                 'sortable'   => false,
        //                 'invisible'  => true,
        //                 'searchable' => true
        //             ]
        //         ]
        //     );
        // });


        // Extend Order backend filter
        // Event::listen('backend.filter.extendScopes', function($widget) {

        //     // Only for the Order controller
        //     if (!$widget->getController() instanceof \Octobro\Octobro\Controllers\Orders) {
        //         return;
        //     }

        //     $widget->addScopes(
        //         [
        //             'coupon_code' => [
        //                 'label'      => 'Is Promotion',
        //                 'type'       => 'checkbox',
        //                 'conditions' => 'coupon_code <> "" or coupon_code IS NOT NULL',
        //             ]
        //         ]
        //     );
        // });

    }
}
