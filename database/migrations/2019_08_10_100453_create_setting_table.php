<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSettingTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('setting', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('end_at')->comment('结束时间');
            $table->text('rule')->comment('规则');
            $table->integer('visit')->default(0)->comment('访问量');
            $table->boolean('anonymity')->default(1)->comment('是否匿名');
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
        Schema::dropIfExists('setting');
    }
}
