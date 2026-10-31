
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('report_forwardings', function (Blueprint $table) {
            $table->id();

            $table->foreignId('report_id')
                ->constrained('reports')
                ->restrictOnDelete();

            $table->foreignId('coordinator_id')
                ->constrained('users')
                ->restrictOnDelete();

            $table->string('recipient_category', 30);
            $table->string('recipient_name', 150);
            $table->text('notes')->nullable();
            $table->dateTime('forwarded_at');

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('report_forwardings');
    }
};
