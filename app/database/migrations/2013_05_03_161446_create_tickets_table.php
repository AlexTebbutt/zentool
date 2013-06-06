<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTicketsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('tickets', function(Blueprint $table)
		{
			$table->integer('id');
			$table->integer('organisationID');
			$table->integer('requesterID');
			$table->integer('assigneeID');
			$table->string('jsonUrl')->nullable();
			$table->string('url');
			$table->text('subject')->nullable();
			$table->string('status');
			$table->integer('time')->default(0);
			$table->timestamp('createdAt');
			$table->timestamp('updatedAt');
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('tickets');
	}

}
