<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\QueryException;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Check if an index exists on a table.
     */
    protected function hasIndex(string $table, string $index): bool
    {
        $connection = Schema::getConnection();
        $driver = $connection->getDriverName();

        if ($driver === 'sqlite') {
            try {
                $indexes = $connection->select("PRAGMA index_list({$table})");
                foreach ($indexes as $idx) {
                    if ($idx->name === $index) {
                        return true;
                    }
                }

                return false;
            } catch (\Exception) {
                return false;
            }
        }

        $database = $connection->getDatabaseName();

        try {
            $result = $connection->select(
                'SELECT COUNT(*) as count FROM information_schema.statistics
                 WHERE table_schema = ? AND table_name = ? AND index_name = ?',
                [$database, $table, $index]
            );

            return $result[0]->count > 0;
        } catch (\Exception) {
            return false;
        }
    }

    /**
     * Run the migrations.
     *
     * Add indexes for frequently queried columns to improve query performance.
     */
    public function up(): void
    {
        if (Schema::hasTable('products')) {
            Schema::table('products', function (Blueprint $table) {
                if (! $this->hasIndex('products', 'products_status_index')) {
                    $table->index(['is_active', 'is_approved', 'is_featured'], 'products_status_index');
                }
                if (! $this->hasIndex('products', 'products_price_index')) {
                    $table->index('price', 'products_price_index');
                }
                if (! $this->hasIndex('products', 'products_type_index')) {
                    $table->index('type', 'products_type_index');
                }
                if (! $this->hasIndex('products', 'products_created_at_index')) {
                    $table->index('created_at', 'products_created_at_index');
                }
                if (! $this->hasIndex('products', 'products_is_new_index')) {
                    $table->index('is_new', 'products_is_new_index');
                }
                if (! $this->hasIndex('products', 'products_is_bookable_index')) {
                    $table->index('is_bookable', 'products_is_bookable_index');
                }
            });
        }

        if (Schema::hasTable('orders')) {
            Schema::table('orders', function (Blueprint $table) {
                if (! $this->hasIndex('orders', 'orders_user_status_index')) {
                    $table->index(['user_id', 'status'], 'orders_user_status_index');
                }
                if (! $this->hasIndex('orders', 'orders_status_payment_index')) {
                    $table->index(['status', 'payment_status'], 'orders_status_payment_index');
                }
                if (! $this->hasIndex('orders', 'orders_payment_created_index')) {
                    $table->index(['payment_status', 'created_at'], 'orders_payment_created_index');
                }
                if (! $this->hasIndex('orders', 'orders_created_at_index')) {
                    $table->index('created_at', 'orders_created_at_index');
                }
                if (! $this->hasIndex('orders', 'orders_total_index')) {
                    $table->index('total', 'orders_total_index');
                }
                if (! $this->hasIndex('orders', 'orders_payment_method_index')) {
                    $table->index('payment_method', 'orders_payment_method_index');
                }
                if (! $this->hasIndex('orders', 'orders_refund_status_index')) {
                    $table->index('refund_status', 'orders_refund_status_index');
                }
            });
        }

        if (Schema::hasTable('cart_items')) {
            Schema::table('cart_items', function (Blueprint $table) {
                if (! $this->hasIndex('cart_items', 'cart_items_user_product_index')) {
                    $table->index(['user_id', 'product_id'], 'cart_items_user_product_index');
                }
                if (! $this->hasIndex('cart_items', 'cart_items_user_product_variant_index')) {
                    $table->index(['user_id', 'product_id', 'variant_id'], 'cart_items_user_product_variant_index');
                }
            });
        }

        if (Schema::hasTable('categories')) {
            Schema::table('categories', function (Blueprint $table) {
                if (! $this->hasIndex('categories', 'categories_status_index')) {
                    $table->index(['is_active', 'is_featured'], 'categories_status_index');
                }
                if (! $this->hasIndex('categories', 'categories_created_at_index')) {
                    $table->index('created_at', 'categories_created_at_index');
                }
            });
        }

        if (Schema::hasTable('users')) {
            Schema::table('users', function (Blueprint $table) {
                if (! $this->hasIndex('users', 'users_role_status_index')) {
                    $table->index(['role', 'is_active'], 'users_role_status_index');
                }
                if (! $this->hasIndex('users', 'users_created_at_index')) {
                    $table->index('created_at', 'users_created_at_index');
                }
            });
        }

        if (Schema::hasTable('product_variants')) {
            Schema::table('product_variants', function (Blueprint $table) {
                if (! $this->hasIndex('product_variants', 'product_variants_product_active_index')) {
                    $table->index(['product_id', 'is_active'], 'product_variants_product_active_index');
                }
            });
        }

        if (Schema::hasTable('product_categories')) {
            Schema::table('product_categories', function (Blueprint $table) {
                if (! $this->hasIndex('product_categories', 'product_categories_category_id_index')) {
                    $table->index('category_id', 'product_categories_category_id_index');
                }
            });
        }

        if (Schema::hasTable('product_ratings')) {
            Schema::table('product_ratings', function (Blueprint $table) {
                if (! $this->hasIndex('product_ratings', 'product_ratings_product_visible_index')) {
                    $table->index(['product_id', 'is_visible'], 'product_ratings_product_visible_index');
                }
                if (! $this->hasIndex('product_ratings', 'product_ratings_rating_index')) {
                    $table->index('rating', 'product_ratings_rating_index');
                }
            });
        }

        if (Schema::hasTable('product_reports')) {
            Schema::table('product_reports', function (Blueprint $table) {
                if (! $this->hasIndex('product_reports', 'product_reports_status_index')) {
                    $table->index('status', 'product_reports_status_index');
                }
                if (! $this->hasIndex('product_reports', 'product_reports_product_status_index')) {
                    $table->index(['product_id', 'status'], 'product_reports_product_status_index');
                }
            });
        }

        if (Schema::hasTable('addresses')) {
            Schema::table('addresses', function (Blueprint $table) {
                if (! $this->hasIndex('addresses', 'addresses_user_default_index')) {
                    $table->index(['user_id', 'is_default'], 'addresses_user_default_index');
                }
                if (! $this->hasIndex('addresses', 'addresses_user_active_index')) {
                    $table->index(['user_id', 'is_active'], 'addresses_user_active_index');
                }
            });
        }

        if (Schema::hasTable('tickets')) {
            Schema::table('tickets', function (Blueprint $table) {
                if (! $this->hasIndex('tickets', 'tickets_user_status_index')) {
                    $table->index(['user_id', 'status'], 'tickets_user_status_index');
                }
                if (! $this->hasIndex('tickets', 'tickets_status_index')) {
                    $table->index('status', 'tickets_status_index');
                }
                if (! $this->hasIndex('tickets', 'tickets_created_at_index')) {
                    $table->index('created_at', 'tickets_created_at_index');
                }
            });
        }

        if (Schema::hasTable('ticket_messages')) {
            Schema::table('ticket_messages', function (Blueprint $table) {
                if (! $this->hasIndex('ticket_messages', 'ticket_messages_ticket_created_index')) {
                    $table->index(['ticket_id', 'created_at'], 'ticket_messages_ticket_created_index');
                }
            });
        }

        if (Schema::hasTable('coupons')) {
            Schema::table('coupons', function (Blueprint $table) {
                if (Schema::hasColumn('coupons', 'end_date') && ! $this->hasIndex('coupons', 'coupons_active_end_date_index')) {
                    $table->index(['is_active', 'end_date'], 'coupons_active_end_date_index');
                } elseif (! $this->hasIndex('coupons', 'coupons_is_active_index')) {
                    $table->index('is_active', 'coupons_is_active_index');
                }
            });
        }

        if (Schema::hasTable('order_refund_requests')) {
            Schema::table('order_refund_requests', function (Blueprint $table) {
                if (! $this->hasIndex('order_refund_requests', 'refund_requests_status_index')) {
                    $table->index('status', 'refund_requests_status_index');
                }
            });
        }

        if (Schema::hasTable('order_logs')) {
            Schema::table('order_logs', function (Blueprint $table) {
                if (! $this->hasIndex('order_logs', 'order_logs_order_created_index')) {
                    $table->index(['order_id', 'created_at'], 'order_logs_order_created_index');
                }
                if (! $this->hasIndex('order_logs', 'order_logs_type_index')) {
                    $table->index('type', 'order_logs_type_index');
                }
            });
        }

        if (Schema::hasTable('wallet_transactions')) {
            Schema::table('wallet_transactions', function (Blueprint $table) {
                if (! $this->hasIndex('wallet_transactions', 'wallet_transactions_user_created_index')) {
                    $table->index(['user_id', 'created_at'], 'wallet_transactions_user_created_index');
                }
            });
        }

        if (Schema::hasTable('offers')) {
            Schema::table('offers', function (Blueprint $table) {
                if (! $this->hasIndex('offers', 'offers_active_dates_index')) {
                    $table->index(['is_active', 'start_date', 'end_date'], 'offers_active_dates_index');
                }
            });
        }

        if (Schema::hasTable('branches')) {
            Schema::table('branches', function (Blueprint $table) {
                if (! $this->hasIndex('branches', 'branches_is_active_index')) {
                    $table->index('is_active', 'branches_is_active_index');
                }
            });
        }

        if (Schema::hasTable('variants')) {
            Schema::table('variants', function (Blueprint $table) {
                if (! $this->hasIndex('variants', 'variants_status_index')) {
                    $table->index(['is_active', 'is_required'], 'variants_status_index');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('products')) {
            $this->dropIndexSafely('products', 'products_status_index');
            $this->dropIndexSafely('products', 'products_price_index');
            $this->dropIndexSafely('products', 'products_type_index');
            $this->dropIndexSafely('products', 'products_created_at_index');
            $this->dropIndexSafely('products', 'products_is_new_index');
            $this->dropIndexSafely('products', 'products_is_bookable_index');
        }

        if (Schema::hasTable('orders')) {
            $this->dropIndexSafely('orders', 'orders_user_status_index');
            $this->dropIndexSafely('orders', 'orders_status_payment_index');
            $this->dropIndexSafely('orders', 'orders_payment_created_index');
            $this->dropIndexSafely('orders', 'orders_created_at_index');
            $this->dropIndexSafely('orders', 'orders_total_index');
            $this->dropIndexSafely('orders', 'orders_payment_method_index');
            $this->dropIndexSafely('orders', 'orders_refund_status_index');
        }

        if (Schema::hasTable('cart_items')) {
            $this->dropIndexSafely('cart_items', 'cart_items_user_product_index');
            $this->dropIndexSafely('cart_items', 'cart_items_user_product_variant_index');
        }

        if (Schema::hasTable('categories')) {
            $this->dropIndexSafely('categories', 'categories_status_index');
            $this->dropIndexSafely('categories', 'categories_created_at_index');
        }

        if (Schema::hasTable('users')) {
            $this->dropIndexSafely('users', 'users_role_status_index');
            $this->dropIndexSafely('users', 'users_created_at_index');
        }

        if (Schema::hasTable('product_variants')) {
            $this->dropIndexSafely('product_variants', 'product_variants_product_active_index');
        }

        if (Schema::hasTable('product_categories')) {
            $this->dropIndexSafely('product_categories', 'product_categories_category_id_index');
        }

        if (Schema::hasTable('product_ratings')) {
            $this->dropIndexSafely('product_ratings', 'product_ratings_product_visible_index');
            $this->dropIndexSafely('product_ratings', 'product_ratings_rating_index');
        }

        if (Schema::hasTable('product_reports')) {
            $this->dropIndexSafely('product_reports', 'product_reports_status_index');
            $this->dropIndexSafely('product_reports', 'product_reports_product_status_index');
        }

        if (Schema::hasTable('addresses')) {
            $this->dropIndexSafely('addresses', 'addresses_user_default_index');
            $this->dropIndexSafely('addresses', 'addresses_user_active_index');
        }

        if (Schema::hasTable('tickets')) {
            $this->dropIndexSafely('tickets', 'tickets_user_status_index');
            $this->dropIndexSafely('tickets', 'tickets_status_index');
            $this->dropIndexSafely('tickets', 'tickets_created_at_index');
        }

        if (Schema::hasTable('ticket_messages')) {
            $this->dropIndexSafely('ticket_messages', 'ticket_messages_ticket_created_index');
        }

        if (Schema::hasTable('coupons')) {
            if ($this->hasIndex('coupons', 'coupons_active_end_date_index')) {
                $this->dropIndexSafely('coupons', 'coupons_active_end_date_index');
            } else {
                $this->dropIndexSafely('coupons', 'coupons_is_active_index');
            }
        }

        if (Schema::hasTable('order_refund_requests')) {
            $this->dropIndexSafely('order_refund_requests', 'refund_requests_status_index');
        }

        if (Schema::hasTable('order_logs')) {
            $this->dropIndexSafely('order_logs', 'order_logs_order_created_index');
            $this->dropIndexSafely('order_logs', 'order_logs_type_index');
        }

        if (Schema::hasTable('wallet_transactions')) {
            $this->dropIndexSafely('wallet_transactions', 'wallet_transactions_user_created_index');
        }

        if (Schema::hasTable('offers')) {
            $this->dropIndexSafely('offers', 'offers_active_dates_index');
        }

        if (Schema::hasTable('branches')) {
            $this->dropIndexSafely('branches', 'branches_is_active_index');
        }

        if (Schema::hasTable('variants')) {
            $this->dropIndexSafely('variants', 'variants_status_index');
        }
    }

    protected function dropIndexSafely(string $table, string $index): void
    {
        if (! $this->hasIndex($table, $index)) {
            return;
        }

        try {
            Schema::table($table, function (Blueprint $blueprint) use ($index) {
                $blueprint->dropIndex($index);
            });
        } catch (QueryException $e) {
            if (! $this->isForeignKeyDependentIndexDropError($e)) {
                throw $e;
            }
        }
    }

    protected function isForeignKeyDependentIndexDropError(QueryException $exception): bool
    {
        $message = $exception->getMessage();

        return str_contains($message, '1553')
            || str_contains($message, 'needed in a foreign key constraint');
    }
};
