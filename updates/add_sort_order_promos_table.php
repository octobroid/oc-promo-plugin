<?php namespace Octommerce\Promo\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class AddSortOrderPromosTable extends Migration
{
    public function up()
    {
        if (Schema::hasColumn('octobro_promo_promos', 'sort_order')) return;
        
        Schema::table('octobro_promo_promos', function($table)
        {
            $table->integer('sort_order')->nullable();
        });
    }

    public function down()
    {
        if (!Schema::hasColumn('octobro_promo_promos', 'sort_order')) return;

        Schema::table('octobro_promo_promos', function($table)
        {
            $table->dropColumn('sort_order');
        });
    }
}