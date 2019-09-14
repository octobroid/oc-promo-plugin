<?php namespace Octobro\Promo\Classes;

/**
 * This is a base class for output
 */
abstract class OutputBase
{

    public $props = [];

    public function outputDetails()
    {
        return [
            'code'        => '',
            'name'        => 'Unknown',
            'description' => 'Unknown rule.',
        ];
    }

    public function registerProperties()
    {
    }

    public function property($key)
    {
        return array_get($this->props, $key);
    }

    public function onHold($couponRedemption, $outputData)
    {
    }

    public function onRedeem($couponRedemption, $outputData)
    {
    }

    public function onRelease($couponRedemption, $outputData)
    {
    }

}