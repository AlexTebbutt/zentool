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
			$table->integer('organisationID')->nullable();
			$table->integer('requesterID');
			$table->integer('assigneeID')->nullable();
			$table->string('jsonUrl')->nullable();
			$table->string('url');
			$table->string('type')->nullable();
			$table->text('subject')->nullable();
			$table->text('description')->nullable();
			$table->string('status')->nullableI();;
			$table->integer('time')->default(0);
			$table->timestamp('createdAt');
			$table->timestamp('updatedAt')->nullable();
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
