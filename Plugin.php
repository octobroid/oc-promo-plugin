<?php namespace Octobro\Promo;

use Event;
use System\Classes\PluginBase;
use Illuminate\Foundation\AliasLoader;

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

    public function register()
    {
        $alias = AliasLoader::getInstance();
        $alias->alias('Promo', 'Octobro\Promo\Facades\Promo');
    }

}
