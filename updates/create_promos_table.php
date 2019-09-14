<?php namespace Octommerce\Promo\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class CreatePromosTable extends Migration
{
    public function up()
    {
        Schema::create('octobro_promo_promos', function($table)
        {
            $table->engine = 'InnoDB';
            $table->increments('id')->unsigned();
            $table->string('name');
            $table->string('slug');
            $table->text('description')->nullable();
            $table->timestamp('start_at')->nullable();
            $table->timestamp('end_at')->nullable();
            $table->boolean('is_active')->default(1);
            $table->boolean('is_published')->default(1);
            $table->text('outputs')->nullable();
            $table->text('success_message')->nullable();
            $table->text('terms_and_conditions')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('octobro_promo_promos');
    }
}