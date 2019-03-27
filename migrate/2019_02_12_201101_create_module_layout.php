<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateModuleLayout extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('modules', function (Blueprint $table) {
            $table->increments('id');
            $table->bigInteger('config_id');
            $table->string('title', 32);
            $table->string('description')->nullable();
            $table->string('code', 32)->nullable();
            $table->tinyInteger('status')->default(0);
        });
        
        Schema::create('modules_setting', function (Blueprint $table) {
            $table->increments('id');
            $table->bigInteger('module_id');
            $table->bigInteger('store_id');
            $table->string('title', 32);
            $table->string('tag', 32);
            $table->string('description')->nullable();
            $table->text('setting');
            $table->tinyInteger('status')->default(1);
        });
        
        Schema::create('modules_route', function (Blueprint $table) {
            $table->increments('id');
            $table->bigInteger('store_id');
            $table->string('route');
            $table->string('title', 32);
            $table->string('description')->nullable();
            $table->tinyInteger('status')->default(1);
        });
        
        Schema::create('modules_setting_to_route', function (Blueprint $table) {
            $table->increments('id');
            $table->bigInteger('module_id');
            $table->bigInteger('modules_setting_id');
            $table->bigInteger('store_id');
            $table->bigInteger('route_id');
            $table->tinyInteger('layout')->default(1);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('modules');
        Schema::dropIfExists('modules_setting');
        Schema::dropIfExists('modules_route');
        Schema::dropIfExists('modules_setting_to_route');
    }
}
