<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        //
        Schema::create('members', function (Blueprint $table) {
            $table->id();
            $table->string("name");
            $table->string("member_code")->unique();
            $table->string("email")->nullable();
            $table->string("phone_number")->nullable();
            $table->string("join_in");
            $table->string("points")->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
