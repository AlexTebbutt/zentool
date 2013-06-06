<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateUsersTable extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('users', function(Blueprint $table) {
            $table->integer('id');
            $table->integer('organisationID');
            $table->string('username',100);
            $table->string('fullname',100);
            $table->string('type',25)->default('user');
            $table->string('email', 255);
            $table->string('password',100);
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
        Schema::drop('users');
    }

}
