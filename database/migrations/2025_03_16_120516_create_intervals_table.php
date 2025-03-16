<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CreateIntervalsTable extends Migration {
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {
        Schema::create('intervals', function(Blueprint $table) {
            $table->id();
            $table->integer('start')->comment('Начало отрезка');
            $table->integer('end')->nullable()->comment('Конец отрезка или NULL для луча');

            $table->index('start', 'intervals_start_idx');
            $table->index('end', 'intervals_end_idx');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {
        Schema::dropIfExists('intervals');
    }
}
