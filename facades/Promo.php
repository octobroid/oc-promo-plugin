<?php namespace Octobro\Promo\Facades;

use October\Rain\Support\Facade;

class Promo extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'octobro.promo';
    }

    protected static function getFacadeInstance()
    {
        return new \Octobro\Promo\Classes\PromoManager;
    }
}
