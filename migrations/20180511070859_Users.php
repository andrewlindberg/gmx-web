<?php

use \GameX\Core\Migration;
use \Illuminate\Database\Schema\Blueprint;

class Users extends Migration {

	/**
	 * Do the migration
	 */
	public function up() {
		$this->getSchema()
			->create($this->getTableName(), function (Blueprint $table) {
				$table->increments('id');
				$table->string('email', 255)->unique();
				$table->string('password', 255);
				$table->unsignedInteger('role_id')->default('0');
				$table->timestamp('last_login')->nullable();
				$table->timestamps();
			});
	}

	/**
	 * Undo the migration
	 */
	public function down() {
		$this->getSchema()->drop($this->getTableName());
	}

	/**
	 * @return string
	 */
	private function getTableName() {
		return 'users';
	}
}
