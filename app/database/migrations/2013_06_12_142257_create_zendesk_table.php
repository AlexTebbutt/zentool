<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateZendeskTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('zendesk', function(Blueprint $table)
		{
			$table->integer('id');
			$table->string('apikey');
			$table->string('user');
			$table->string('subdomain');
			$table->string('suffix');
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('zendesk');
	}

}
