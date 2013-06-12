<?php

class ZendeskTableSeeder extends Seeder {

    public function run()
    {
    	// Uncomment the below to wipe the table clean before populating
    	// DB::table('users')->delete();

        $zendesk = array(
	        'id' => '1',
	        'apikey' => 'ra75ePE6HPIFv5NFRQeCdcWew6F4BJ626y535kFQ',
					'user' => 'alex.tebbutt@images.co.uk',
        	'subdomain' => 'imagesandco',
        	'suffix' => '.json');

        // Uncomment the below to run the seeder
        DB::table('zendesk')->insert($zendesk);
    }

}