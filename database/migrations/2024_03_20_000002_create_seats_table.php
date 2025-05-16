<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('seats', function (Blueprint $table) {
            $table->id();
            $table->foreignId('event_id')->constrained()->cascadeOnDelete();
            $table->unsignedInteger('row');
            $table->unsignedInteger('column');
            $table->enum('status', ['available', 'reserved', 'booked'])->default('available');
            $table->timestamp('reservation_expires_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['event_id', 'row', 'column']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('seats');
    }
};
