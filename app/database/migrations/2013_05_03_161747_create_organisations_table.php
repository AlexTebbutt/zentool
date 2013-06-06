<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateOrganisationsTable extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('organisations', function(Blueprint $table) {
			$table->integer('id');
			$table->string('name', 255);
			$table->string('jsonUrl',255);
			$table->string('url',255);
			$table->string('accountType',10)->nullable();
			$table->integer('rollingTime')->default(0);
			$table->integer('timeOnAccount')->default(0);
			$table->boolean('active')->default(1);
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
        Schema::drop('organisations');
    }

}
