<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateHistoryTable extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('history', function(Blueprint $table) {
					$table->increments('id');
					$table->integer('organisationID');
					$table->integer('timeAdjustment');
					$table->integer('timeOnAccount');
					$table->text('detail');
					$table->timestamp('createdAt');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('history');
    }

}
