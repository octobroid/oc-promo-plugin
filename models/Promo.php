<?php namespace Octobro\Promo\Models;

use Model;
use Carbon\Carbon;

/**
 * Model
 */
class Promo extends Model
{
    use \October\Rain\Database\Traits\Validation;
    use \October\Rain\Database\Traits\Sluggable;

    /*
     * Validation
     */
    public $rules = [
        'name' => 'required',
    ];

    public $dates = ['start_at', 'end_at'];

    public $jsonable = ['output'];

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
        'promo_rules' => [
            'Octobro\Promo\Models\Rule',
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
}