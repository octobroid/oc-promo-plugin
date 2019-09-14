<?php namespace Octommerce\Promo\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class CreateCouponsTable extends Migration
{
    public function up()
    {
        Schema::create('octobro_promo_coupons', function($table)
        {
            $table->engine = 'InnoDB';
            $table->increments('id')->unsigned();
            $table->string('code');
            $table->integer('stock')->nullable()->unsigned();
            $table->integer('stock_used')->unsigned()->default(0);
            $table->timestamp('start_at')->nullable();
            $table->timestamp('end_at')->nullable();
            $table->integer('promo_id')->nullable()->unsigned()->index();
            $table->integer('user_id')->nullable()->unsigned()->index();
            $table->integer('usage_limit')->nullable()->unsigned()->default(0);
            $table->integer('usage_limit_interval')->nullable()->unsigned();
            $table->enum('usage_limit_interval_unit', ['minute', 'hour', 'day', 'week', 'month', 'year'])->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('octobro_promo_coupons');
    }
}