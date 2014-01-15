<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Config;

class CreateModulesTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create(Config::get('modules.table'), function(Blueprint $table)
		{
			$table->increments('id')->unsigned();
			$table->string('name', 255);
			$table->enum('status', ['enabled', 'disabled'])->default('disabled');
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::dropIfExists(Config::get('modules.table'));
	}

}