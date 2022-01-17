<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePermissionTables extends Migration
{
    public function up()
    {

        /**
         * 操作权限
         */
        Schema::create('permissions', function (Blueprint $table) {
            $table->id();
            $table->integer('parent_id')->default(0)->comment('父级ID')->index();
            $table->string('name')->comment('名字')->index();
            $table->string('key')->comment('键值')->unique()->index();
            $table->boolean('is_active')->default(false)->comment('是否激活')->index();
            $table->integer('sort_order')->default(0)->comment('排序，数字越小越靠前');
            $table->timestamps();
        });

        /**
         * 角色
         */
        Schema::create('roles', function (Blueprint $table) {
            $table->id();
            $table->string('name')->index()->comment('名称');
            $table->boolean('is_active')->default(false)->comment('是否激活')->index();
            $table->string('key')->nullable()->index()->comment('等级标注');
            $table->string('remark')->nullable()->comment('备注');
            $table->integer('sort_order')->default(0)->comment('排序，数字越小越靠前');
            $table->timestamps();
        });

        DB::table('roles')->insert(['id' => 1, 'name' => '超级管理员', 'key' => 'admin', 'is_active' => true]);


        Schema::create('user_role', function (Blueprint $table) {
            $table->id();
            $table->integer('user_id')->comment('用户ID')->index();
            $table->integer('role_id')->comment('用户组ID')->index();
            $table->timestamps();
        });

        /**
         * 用户权限
         */
        Schema::create('user_permission', function (Blueprint $table) {
            $table->id();
            $table->integer('user_id')->comment('用户ID')->index();
            $table->integer('permission_id')->comment('权限ID')->index();
            $table->timestamps();
        });

        /**
         * 角色权限
         */
        Schema::create('role_permission', function (Blueprint $table) {
            $table->id();
            $table->integer('role_id')->comment('角色ID')->index();
            $table->integer('permission_id')->comment('权限ID')->index();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('permissions');
        Schema::dropIfExists('roles');
        Schema::dropIfExists('user_role');
        Schema::dropIfExists('user_permission');
        Schema::dropIfExists('role_permission');
    }
}
