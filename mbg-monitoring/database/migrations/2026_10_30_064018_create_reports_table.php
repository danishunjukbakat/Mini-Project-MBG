
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('reports', function (Blueprint $table) {
            $table->id();

            $table->foreignId('reporter_id')
                ->constrained('users')
                ->restrictOnDelete();

            $table->foreignId('distribution_id')
                ->constrained('distributions')
                ->restrictOnDelete();

            $table->string('title', 150);
            $table->date('incident_date');
            $table->text('description');

            $table->string('evidence_path')->nullable();

            $table->string('status', 30)
                ->default('submitted');

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reports');
    }
};
