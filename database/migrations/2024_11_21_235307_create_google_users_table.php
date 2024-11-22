<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateGoogleUsersTable extends Migration
{
    public function up()
    {
        Schema::create('google_users', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid');
            $table->string('google_id')->unique();
            $table->string('name');
            $table->string('email')->unique();
            $table->string('avatar')->nullable();
            $table->string('access_token', 512)->nullable();
            $table->string('refresh_token')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('google_users');
    }
}
