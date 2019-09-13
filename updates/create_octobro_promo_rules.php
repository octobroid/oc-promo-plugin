<?php namespace Octommerce\Promo\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableCreateBigmangoPromoRules extends Migration
{
    public function up()
    {
        Schema::create('octobro_promo_rules', function($table)
        {
            $table->engine = 'InnoDB';
            $table->increments('id')->unsigned();
            $table->integer('promo_id')->unsigned()->nullable()->index();
            $table->string('type');
            $table->text('options')->nullable();
            $table->text('error_message')->nullable();
            $table->integer('sort_order')->nullable()->unsigned();
            $table->enum('operator', ['and', 'or', 'xor'])->nullable()->default('and');
            $table->text('description')->nullable();
            $table->string('output_type')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('octobro_promo_rules');
    }
}