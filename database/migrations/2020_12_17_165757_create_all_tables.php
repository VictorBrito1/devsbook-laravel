<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAllTables extends Migration
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
            $table->string('email', 100);
            $table->string('password', 200);
            $table->string('name', 100);
            $table->date('birth_date');
            $table->string('city', 100)->nullable();
            $table->string('work', 100)->nullable();
            $table->string('avatar', 100)->default('default.jpg');
            $table->string('cover', 100)->default('cover.jpg');
            $table->string('token', 200)->nullable();
        });

        Schema::create('user_relations', function (Blueprint $table) {
            $table->id();
            $table->integer('user_from');
            $table->integer('user_to');
        });

        Schema::create('posts', function (Blueprint $table) {
            $table->id();
            $table->integer('id_user');
            $table->string('type', 20);
            $table->dateTime('created_at');
            $table->text('body');
        });

        Schema::create('post_likes', function (Blueprint $table) {
            $table->id();
            $table->integer('id_post');
            $table->integer('id_user');
            $table->dateTime('created_at');
        });

        Schema::create('post_comments', function (Blueprint $table) {
            $table->id();
            $table->integer('id_post');
            $table->integer('id_user');
            $table->dateTime('created_at');
            $table->text('body');
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
        Schema::dropIfExists('user_relations');
        Schema::dropIfExists('posts');
        Schema::dropIfExists('post_likes');
        Schema::dropIfExists('post_comments');
    }
}
