<?php namespace Octobro\Promo\Models;

use Model;
use Promo as PromoManager;
use Carbon\Carbon;

/**
 * Model
 */
class Promo extends Model
{
    use \October\Rain\Database\Traits\Sluggable;
    use \October\Rain\Database\Traits\Sortable;

    public $dates = ['start_at', 'end_at'];


    public $jsonable = ['rules', 'outputs'];

    /**
     * @var array Generate slugs for these attributes.
     */
    protected $slugs = ['slug' => 'name'];

    /**
     * @var string The database table used by the model.
     */
    public $table = 'octobro_promo_promos';

    public $hasMany = [
        'coupons' => [
            'Octobro\Promo\Models\Coupon',
            'delete' => true,
        ],
    ];

    public $attachOne = [
        'image' => 'System\Models\File',
    ];

    public function getStatusAttribute()
    {
        $now = Carbon::now();

        if ($this->start_at > $now) {
            return 'waiting';
        } elseif ($this->end_at > $now) {
            return 'running';
        } else {
            return 'ended';
        }
    }

    public function getRuleCodeOptions()
    {
        return array_merge(
            collect(PromoManager::listRules())->lists('name', 'code'),
            [
                'group' => 'Rule Group',
            ]
        );
    }

    public function getOutputCodeOptions()
    {
        return collect(PromoManager::listOutputs())->lists('name', 'code');
    }
}