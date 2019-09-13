<?php namespace Octobro\Promo\Models;

use Db;
use Model;
use Event;
use Exception;

/**
 * Model
 */
class CouponRedemption extends Model
{
    use \October\Rain\Database\Traits\Validation;

    const STATUS_PENDING = 'pending';
    const STATUS_SUCCESS = 'success';
    const STATUS_EXPIRED = 'expired';

    /*
     * Validation
     */
    public $rules = [
        'coupon_code' => 'required',
    ];

    public $jsonable = ['data'];

    /**
     * @var string The database table used by the model.
     */
    public $table = 'octobro_promo_coupon_redemptions';

    public $belongsTo = [
        'coupon' => 'Octobro\Promo\Models\Coupon',
        'user'   => 'RainLab\User\Models\User',
    ];

    public function beforeCreate()
    {
        $this->coupon_code = $this->coupon ? $this->coupon->code : null;
        $this->status      = self::STATUS_PENDING;
    }

    public function redeem()
    {
        // If already redeemed
        if ($this->status == self::STATUS_SUCCESS) {
            return;
        }

        try {
            Db::beginTransaction();

            $this->status = self::STATUS_SUCCESS;
            $this->save();

            Event::fire('octobro.promo.afterCouponRedeemed', [$this]);

            Db::commit();
        }
        catch (Exception $e) {
            Db::rollBack();
            throw $e;
        }
    }

    public function release()
    {
        // If already released
        if( $this->status == self::STATUS_EXPIRED) {
            return;
        }

        try {
            Db::beginTransaction();

            $this->status = self::STATUS_EXPIRED;
            $this->save();

            $this->coupon->stock_used -= $this->amount;
            $this->coupon->save();

            Event::fire('octobro.promo.afterCouponReleased', [$this]);

            Db::commit();
        }
        catch (Exception $e) {
            Db::rollBack();
            throw $e;
        }
    }
}