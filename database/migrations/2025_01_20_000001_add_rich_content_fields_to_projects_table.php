<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Skip if using MongoDB (NoSQL doesn't need schema changes)
        $driver = config('database.connections.legacy.driver');
        if ($driver === 'mongodb') {
            // MongoDB automatically handles new fields - no migration needed
            return;
        }
        
        // Add rich content fields to projects table
        Schema::connection('legacy')->table('projects', function (Blueprint $table) {
            // Map and location
            $table->text('map_embed_code')->nullable()->after('description');
            $table->decimal('latitude', 10, 8)->nullable()->after('map_embed_code');
            $table->decimal('longitude', 11, 8)->nullable()->after('latitude');
            $table->text('surrounding_area')->nullable()->after('longitude');
            
            // Designs and drawings
            $table->text('proposed_designs')->nullable()->after('surrounding_area');
            $table->text('drawings')->nullable()->after('proposed_designs');
            
            // Additional rich content
            $table->text('location_details')->nullable()->after('drawings');
            $table->text('neighborhood_info')->nullable()->after('location_details');
            $table->text('development_plans')->nullable()->after('neighborhood_info');
            
            // Visibility toggles for public frontend
            $table->boolean('show_to_investors')->default(true)->after('development_plans');
            $table->boolean('show_map')->default(true)->after('show_to_investors');
            $table->boolean('show_surrounding_area')->default(true)->after('show_map');
            $table->boolean('show_designs')->default(true)->after('show_surrounding_area');
            $table->boolean('show_drawings')->default(true)->after('show_designs');
            $table->boolean('show_location_details')->default(true)->after('show_drawings');
            $table->boolean('show_neighborhood_info')->default(true)->after('show_location_details');
            $table->boolean('show_development_plans')->default(true)->after('show_neighborhood_info');
        });
    }

    public function down(): void
    {
        // Skip if using MongoDB
        $driver = config('database.connections.legacy.driver');
        if ($driver === 'mongodb') {
            return;
        }
        
        Schema::connection('legacy')->table('projects', function (Blueprint $table) {
            $table->dropColumn([
                'map_embed_code',
                'latitude',
                'longitude',
                'surrounding_area',
                'proposed_designs',
                'drawings',
                'location_details',
                'neighborhood_info',
                'development_plans',
                'show_to_investors',
                'show_map',
                'show_surrounding_area',
                'show_designs',
                'show_drawings',
                'show_location_details',
                'show_neighborhood_info',
                'show_development_plans',
            ]);
        });
    }
};

