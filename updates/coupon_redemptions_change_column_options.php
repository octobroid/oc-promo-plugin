<?php namespace Octommerce\Promo\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class CouponRedemptionsChangeColumnOptions extends Migration
{
    public function up()
    {
        Schema::table('octobro_promo_coupon_redemptions', function($table)
        {
            $table->longText('options')->nullable()->change();
        });
    }

    public function down()
    {
        
    }
}