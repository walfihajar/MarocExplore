<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('destination_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('destination_id')->constrained("destinations")->onDelete('cascade'); // Lien avec la destination
            $table->string('type');
            $table->string('nom');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('destination_details');
    }
};
