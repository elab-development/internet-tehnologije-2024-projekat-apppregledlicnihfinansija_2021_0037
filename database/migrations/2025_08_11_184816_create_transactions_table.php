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
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
    
            // Povezano sa korisnikom koji je dodao transakciju
            $table->unsignedBigInteger('user_id');
    
            // Tip transakcije: income (prihod) ili expense (rashod)
            $table->enum('type', ['income', 'expense']);
    
            // Osnovni podaci o transakciji
            $table->string('title'); // npr. "Plata", "Račun za struju"
            $table->decimal('amount', 10, 2); // iznos
            $table->date('date'); // datum kada se desilo
    
            // Povezano sa kategorijama
            $table->unsignedBigInteger('category_id')->nullable();
    
            // Opis (opciono)
            $table->text('description')->nullable();
    
            $table->timestamps();
    
            // Strani ključevi
            $table->foreign('user_id', 'transactions_user_id_fk')
      ->references('id')
      ->on('users')
      ->onDelete('cascade');

        $table->foreign('category_id', 'transactions_category_id_fk')
            ->references('id')
            ->on('categories')
            ->onDelete('set null');

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};
