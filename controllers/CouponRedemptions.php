<?php namespace Octobro\Promo\Controllers;

use BackendMenu;
use Backend\Classes\Controller;

/**
 * Coupon Redemptions Back-end Controller
 */
class CouponRedemptions extends Controller
{
    public $implement = [
        'Backend.Behaviors.FormController',
        'Backend.Behaviors.ListController'
    ];

    public $formConfig = 'config_form.yaml';
    public $listConfig = 'config_list.yaml';

    public function __construct()
    {
        parent::__construct();

        BackendMenu::setContext('Octobro.Promo', 'promo', 'couponredemptions');
    }
}
