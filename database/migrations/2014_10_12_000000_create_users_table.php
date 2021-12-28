<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

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
            $table->id();
            $table->string('name', 30);
            $table->string('email', 40)->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->enum('job', ['employee', 'executive', 'humanresources']);
            $table->double('salary', 12, 2);
            $table->string('biography', 500);
            $table->string('profileImgUrl');
            $table->string('api_token')->unique()->nullable();
            $table->rememberToken();
            $table->timestamps();
        });
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
