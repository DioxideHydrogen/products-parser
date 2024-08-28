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
        Schema::create('product_import_controls', function (Blueprint $table) {
            $table->id('code');
			$table->string('file_name');
			$table->dateTimeTz('imported_t');
            $table->dateTimeTz('created_t')->useCurrent();
            $table->dateTimeTz('updatedd_t')->useCurrentOnUpdate();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_import_controls');
    }
};
