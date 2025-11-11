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
        // Add indexes to orders table
        Schema::table('orders', function (Blueprint $table) {
            $table->index('table_id', 'idx_orders_table_id');
            $table->index('status', 'idx_orders_status');
            $table->index('created_at', 'idx_orders_created_at');
            $table->index(['status', 'table_id'], 'idx_orders_status_table');
        });

        // Add indexes to order_items table
        Schema::table('order_items', function (Blueprint $table) {
            $table->index('order_id', 'idx_order_items_order_id');
            $table->index('food_id', 'idx_order_items_food_id');
            $table->index(['order_id', 'food_id'], 'idx_order_items_order_food');
        });

        // Add indexes to foods table
        Schema::table('food', function (Blueprint $table) {
            $table->index('type', 'idx_food_type');
            $table->index('is_available', 'idx_food_is_available');
            $table->index(['type', 'is_available'], 'idx_food_type_available');
            $table->index('name', 'idx_food_name');
        });

        // Add indexes to tables table
        Schema::table('tables', function (Blueprint $table) {
            $table->index('is_available', 'idx_tables_is_available');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Drop indexes from orders table
        Schema::table('orders', function (Blueprint $table) {
            $table->dropIndex('idx_orders_table_id');
            $table->dropIndex('idx_orders_status');
            $table->dropIndex('idx_orders_created_at');
            $table->dropIndex('idx_orders_status_table');
        });

        // Drop indexes from order_items table
        Schema::table('order_items', function (Blueprint $table) {
            $table->dropIndex('idx_order_items_order_id');
            $table->dropIndex('idx_order_items_food_id');
            $table->dropIndex('idx_order_items_order_food');
        });

        // Drop indexes from foods table
        Schema::table('food', function (Blueprint $table) {
            $table->dropIndex('idx_food_type');
            $table->dropIndex('idx_food_is_available');
            $table->dropIndex('idx_food_type_available');
            $table->dropIndex('idx_food_name');
        });

        // Drop indexes from tables table
        Schema::table('tables', function (Blueprint $table) {
            $table->dropIndex('idx_tables_is_available');
        });
    }
};
