<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// phpcs:ignore PSR1.Classes.ClassDeclaration.MissingNamespace
class CreateSearchesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('searches', function (Blueprint $table) {
            $table->string('guid');
            $table->string('search_query', 2048);
            $table->timestamp('last_seen');
            $table->timestamp('changed_at', 6);
            $table->unique(['guid', 'search_query']);
        });
    }
    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('searches');
    }
}
