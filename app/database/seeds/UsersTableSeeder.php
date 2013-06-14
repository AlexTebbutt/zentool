<?php

class UsersTableSeeder extends Seeder {

    public function run()
    {
    	// Uncomment the below to wipe the table clean before populating
    	// DB::table('users')->delete();

        $users = array('id' => '1',
					'organisationID' => '1',
        	'username' => 'admin',
        	'fullname' => 'Alex Tebbutt',
        	'type' => 'admin',
        	'email' => 'alex.tebbutt@images.co.uk',
        	'password' => Hash::make('admin01!!'),
        	'active' => '1',
        	'zendeskUser' => '0' );

        // Uncomment the below to run the seeder
        DB::table('users')->insert($users);
    }

}