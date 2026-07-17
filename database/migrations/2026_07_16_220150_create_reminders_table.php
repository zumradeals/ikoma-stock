<?php

use App\Enums\ReminderChannel;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Journal append-only des relances effectuées.
     */
    public function up(): void
    {
        Schema::create('reminders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained('companies')->restrictOnDelete();
            $table->foreignId('receivable_id')->constrained('receivables')->restrictOnDelete();
            $table->foreignId('user_id')->constrained('users')->restrictOnDelete();
            $table->date('reminder_date');
            $table->string('channel', 20);
            $table->text('message_sent')->nullable();
            $table->text('customer_response')->nullable();
            $table->timestamp('next_reminder_scheduled_at')->nullable();
            $table->timestamp('created_at')->useCurrent();
        });

        $channels = implode(',', array_map(fn ($v) => "'{$v}'", ReminderChannel::values()));
        if (DB::getDriverName() !== 'sqlite') { DB::statement("ALTER TABLE reminders ADD CONSTRAINT chk_reminders_channel CHECK (channel IN ({$channels}))"); }
    }

    public function down(): void
    {
        Schema::dropIfExists('reminders');
    }
};
