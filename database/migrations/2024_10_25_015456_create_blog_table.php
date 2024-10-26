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
        Schema::create('blog', function (Blueprint $collection) {
            $collection->uuid('uuid')->primary();
            $collection->string('key')->unique(); // Unique key for identifying the Markdown file
            $collection->string('content');       // Encoded Markdown content
            $collection->string('hash');          // Hash of the content for integrity
            $collection->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('blog');
    }
};
