<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('provider_fields', function (Blueprint $table) {
            $table->id();
            $table->string('uid');
            $table->string('provider_id');
            $table->json('form_schema')->nullable();
            $table->tinyInteger('status')->default(0);
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('provider_fields');
    }
};

/*
INSERT INTO provider_fields (uid, provider_id, form_schema, status, created_at, updated_at)
VALUES (
    UUID(),  -- or replace with a fixed string if UUID() is not supported
    (SELECT id FROM providers WHERE uid = 'amadeus' LIMIT 1),
    '[
        { "label": "First Name", "name": "first_name", "type": "text", "required": true },
        { "label": "Last Name", "name": "last_name", "type": "text", "required": true },
        { "label": "Gender", "name": "gender", "type": "select", "options": { "MALE": "Male", "FEMALE": "Female" }, "required": true }
    ]',
    1,
    NOW(),
    NOW()
);
*/