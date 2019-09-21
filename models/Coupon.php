<?php namespace Octobro\Promo\Models;

use Db;
use Model;
use Event;
use Promo;
use Carbon\Carbon;

/**
 * Model
 */
class Coupon extends Model
{
    use \October\Rain\Database\Traits\Validation;

    /*
     * Validation
     */
    public $rules = [
        'code' => 'required',
        // 'stock' => 'required',
    ];

    /**
     * @var string The database table used by the model.
     */
    public $table = 'octobro_promo_coupons';

    /**
     * @var array Guarded fields
     */
    protected $guarded = [];

    /**
     * @var array Fillable fields
     */
    protected $fillable = [];

    public $belongsTo = [
        'promo' => 'Octobro\Promo\Models\Promo',
        'user' => 'RainLab\User\Models\User',
    ];

    public $hasMany = [
        'redemptions' => 'Octobro\Promo\Models\CouponRedemption',
    ];

    /**
     * [hold description]
     * @param  [type]  $user_id    [description]
     * @param  integer $amount     [description]
     * @param  integer $time_limit [description]
     * @param  [type]  $data       [description]
     * @return [type]              [description]
     */
    public function hold($user, $amount = 1, $options = null, $outputs = null)
    {
        try {
            Db::beginTransaction();

            if (!is_null($this->stock) && $this->stock_used + $amount > $this->stock) {
                return false;
            }

            $this->stock_used += $amount;
            $this->save();

            $redemption          = new CouponRedemption;
            $redemption->user    = $user;
            $redemption->coupon  = $this;
            $redemption->amount  = $amount;
            $redemption->options = $options;
            $redemption->outputs = $outputs;
            // $redemption->expired_at = Carbon::now()->addSeconds($time_limit);
            $redemption->save();

            Promo::applyOutputs($redemption, 'hold');

            Event::fire('octobro.promo.afterCouponHeld', [$redemption]);

            Db::commit();

            return $redemption;
        }
        catch(\Exception $e) {
            Db::rollBack();
            throw $e;
        }
    }

    static function generate($promo_id, $amount = 1, $length = 12, $stock = null, $user = null, $options = [])
    {
        for ($i = 0; $i < $amount; $i++) {
            self::create([
                'promo_id' => $promo_id,
                'code'     => self::generateCode(),
                'stock'    => $stock,
            ]);
        }
    }

    static function generateCode($length = 12)
    {
        $characters = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789';
        $charactersLength = strlen($characters);

        $code = '';
    
        for ($i = 0; $i < $length; ++$i) {
            $code .= $characters[rand(0, $charactersLength - 1)];
        }

        while (self::whereCode($code)->count()) {
            $code = self::generateCode();
        }

        return $code;
    }
}