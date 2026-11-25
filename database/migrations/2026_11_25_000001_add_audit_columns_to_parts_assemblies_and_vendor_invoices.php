<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Part, Assembly and VendorInvoice use HasAuditFields, which sets created_by
 * and updated_by whenever a user is logged in, but their tables were missing
 * those columns, so creating or updating them from the app failed.
 */
return new class extends Migration
{
    /** @var array<string, list<string>> */
    private array $columns = [
        'parts' => ['created_by', 'updated_by'],
        'assemblies' => ['created_by', 'updated_by'],
        'vendor_invoices' => ['updated_by'],
    ];

    public function up(): void
    {
        foreach ($this->columns as $tableName => $columns) {
            Schema::table($tableName, function (Blueprint $table) use ($tableName, $columns): void {
                foreach ($columns as $column) {
                    if (! Schema::hasColumn($tableName, $column)) {
                        $table->foreignId($column)->nullable()->constrained('users')->nullOnDelete();
                    }
                }
            });
        }
    }

    public function down(): void
    {
        foreach ($this->columns as $tableName => $columns) {
            Schema::table($tableName, function (Blueprint $table) use ($tableName, $columns): void {
                foreach (array_reverse($columns) as $column) {
                    if (Schema::hasColumn($tableName, $column)) {
                        $table->dropConstrainedForeignId($column);
                    }
                }
            });
        }
    }
};
