<?php

class Option extends Eloquent {
    protected $guarded = array();
		protected $table = 'options';
    public static $rules = array();
    public $timestamps = false;

		public function getOptions() {
			
			return json_decode($this->options);
			
		}
		
/*
		public function getOptionsAttribute($value)
		{
			
			return json_decode($value);
			
		}
*/

}