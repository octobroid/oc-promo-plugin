<?php namespace Octommerce\Promo\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableCreateBigmangoPromoCouponRedemptions extends Migration
{
    public function up()
    {
        Schema::create('octobro_promo_coupon_redemptions', function($table)
        {
            $table->engine = 'InnoDB';
            $table->increments('id')->unsigned();
            $table->string('coupon_code');
            $table->integer('coupon_id')->nullable()->unsigned()->index();
            $table->integer('user_id')->nullable()->unsigned()->index();
            $table->integer('amount')->default(1)->unsigned();
            $table->text('data')->nullable();
            $table->string('status')->nullable();
            $table->timestamp('expired_at')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('octobro_promo_coupon_redemptions');
    }
}