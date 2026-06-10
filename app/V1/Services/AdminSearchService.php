<?php

namespace App\V1\Services;

use App\Models\Order;
use App\Models\Ticket;
use App\Models\User;
use App\V1\Repositories\CategoryRepository;
use App\V1\Repositories\ProductRepository;
use App\V1\Support\AdminSearchIndex;
use Illuminate\Support\Collection;

class AdminSearchService
{
    public function __construct(
        protected ProductRepository $products,
        protected CategoryRepository $categories,
    ) {}

    /**
     * @return list<array{title: string, group: string, url: string, icon: string, subtitle?: string}>
     */
    public function search(string $query, int $limit = 15): array
    {
        $query = trim($query);

        if (mb_strlen($query) < 2) {
            return [];
        }

        $results = collect(AdminSearchIndex::filterPages($query))
            ->merge($this->searchProducts($query))
            ->merge($this->searchCategories($query))
            ->merge($this->searchOrders($query))
            ->merge($this->searchCustomers($query))
            ->merge($this->searchTickets($query));

        return $results
            ->unique(fn (array $item) => $item['url'])
            ->take($limit)
            ->values()
            ->all();
    }

    /**
     * @return Collection<int, array{title: string, group: string, url: string, icon: string, subtitle?: string}>
     */
    protected function searchProducts(string $query): Collection
    {
        return $this->products->search($query)
            ->take(5)
            ->map(fn ($product) => [
                'title' => $product->getTranslation('name', app()->getLocale()),
                'subtitle' => __('messages.Product').' #'.$product->id.' · '.$product->sku,
                'group' => __('messages.Catalog'),
                'url' => route('v1.admin.products.edit', $product),
                'icon' => 'box-seam',
            ]);
    }

    /**
     * @return Collection<int, array{title: string, group: string, url: string, icon: string, subtitle?: string}>
     */
    protected function searchCategories(string $query): Collection
    {
        return $this->categories->search($query)
            ->take(5)
            ->map(fn ($category) => [
                'title' => $category->getTranslation('name', app()->getLocale()),
                'subtitle' => __('messages.Category').' · '.$category->slug,
                'group' => __('messages.Catalog'),
                'url' => route('v1.admin.categories.edit', $category),
                'icon' => 'grid',
            ]);
    }

    /**
     * @return Collection<int, array{title: string, group: string, url: string, icon: string, subtitle?: string}>
     */
    protected function searchOrders(string $query): Collection
    {
        $builder = Order::query();

        if (ctype_digit($query)) {
            $builder->where('id', (int) $query);
        } else {
            $term = '%'.mb_strtolower($query).'%';
            $builder->where(function ($q) use ($term) {
                $q->whereRaw('LOWER(customer_name) LIKE ?', [$term])
                    ->orWhereRaw('LOWER(customer_email) LIKE ?', [$term])
                    ->orWhereRaw('LOWER(customer_phone) LIKE ?', [$term])
                    ->orWhereRaw('LOWER(payment_method) LIKE ?', [$term])
                    ->orWhereHas('user', function ($userQuery) use ($term) {
                        $userQuery->whereRaw('LOWER(name) LIKE ?', [$term])
                            ->orWhereRaw('LOWER(email) LIKE ?', [$term])
                            ->orWhereRaw('LOWER(phone) LIKE ?', [$term]);
                    });
            });
        }

        return $builder->latest()->limit(5)->get()->map(fn (Order $order) => [
            'title' => __('messages.Order #:id', ['id' => $order->id]),
            'subtitle' => trim(implode(' · ', array_filter([
                $order->customer_name,
                $order->payment_method ? ucfirst(str_replace('_', ' ', (string) $order->payment_method)) : null,
                $order->payment_status,
            ]))),
            'group' => __('messages.Sales'),
            'url' => route('v1.admin.orders.show', $order),
            'icon' => 'cart-check',
        ]);
    }

    /**
     * @return Collection<int, array{title: string, group: string, url: string, icon: string, subtitle?: string}>
     */
    protected function searchCustomers(string $query): Collection
    {
        $term = '%'.mb_strtolower($query).'%';

        return User::query()
            ->role('user')
            ->where(function ($q) use ($term) {
                $q->whereRaw('LOWER(name) LIKE ?', [$term])
                    ->orWhereRaw('LOWER(email) LIKE ?', [$term])
                    ->orWhereRaw('LOWER(phone) LIKE ?', [$term]);
            })
            ->latest()
            ->limit(5)
            ->get()
            ->map(fn (User $user) => [
                'title' => $user->name,
                'subtitle' => trim(implode(' · ', array_filter([$user->email, $user->phone]))),
                'group' => __('messages.Admins and Customers'),
                'url' => route('v1.admin.customers.show', $user),
                'icon' => 'person',
            ]);
    }

    /**
     * @return Collection<int, array{title: string, group: string, url: string, icon: string, subtitle?: string}>
     */
    protected function searchTickets(string $query): Collection
    {
        $term = '%'.mb_strtolower($query).'%';

        return Ticket::query()
            ->where(function ($q) use ($term) {
                $q->whereRaw('LOWER(subject) LIKE ?', [$term])
                    ->orWhereRaw('LOWER(description) LIKE ?', [$term]);
            })
            ->latest()
            ->limit(5)
            ->get()
            ->map(fn (Ticket $ticket) => [
                'title' => $ticket->subject,
                'subtitle' => __('messages.Ticket').' #'.$ticket->id,
                'group' => __('messages.Rating & Support'),
                'url' => route('v1.admin.tickets.show', $ticket),
                'icon' => 'chat-dots',
            ]);
    }
}
