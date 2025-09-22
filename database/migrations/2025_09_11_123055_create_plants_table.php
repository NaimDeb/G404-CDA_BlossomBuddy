<?php

use App\Models\Plant;
use App\Models\User;
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
        Schema::create('plants', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->id();
            $table->string("common_name");
            $table->json("watering_general_benchmark")->nullable();
            $table->string('api_id')->nullable();
            $table->string('watering')->nullable();
            $table->boolean('flowers')->default(false);
            $table->boolean('fruits')->default(false);
            $table->boolean('leaf')->default(false);
            $table->string('growth_rate')->nullable();
            $table->string('maintenance')->nullable();
            $table->timestamps()->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('plants');
    }
};
