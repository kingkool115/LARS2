<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('users', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
            $table->string('email')->unique();
            $table->string('password');
            $table->string('verifyToken')->nullable(); // this column will be a VARCHAR with no default value and will also BE NULLABLE
            $table->boolean('status')->default(0); // this column will be a TINYINT with a default value of 0 , [0 for false & 1 for true i.e. verified]
            $table->rememberToken();
            $table->timestamps();
        });

        DB::table('users')->insert(
            array(
                'email' => 'presentation_user',
                'verified' => true
            )
        );
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('users');
    }
}
