<?php

class OrganisationsTableSeeder extends Seeder {

    public function run()
    {
        $organisations = array(
        	'id' => '1',
        	'name' => 'Administrator',
        	'jsonUrl' => '',
        	'url' => '',
        	'accountType' => '',
        	'rollingTime' => '',
        	'timeOnAccount' => '0',
        	'active' => '1',
        	'createdAt' => date('Y-m-d')

        );

        // Uncomment the below to run the seeder
        DB::table('organisations')->insert($organisations);
    }

}