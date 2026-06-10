<?php

namespace App\V1\Http\Controllers\Web\Admin;

use App\Models\User;
use App\V1\Http\Requests\Web\Admin\StoreCustomerRequest;
use App\V1\Http\Requests\Web\Admin\UpdateCustomerRequest;
use App\V1\Services\CustomerService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class CustomerController extends AdminController
{
    public function __construct(
        protected CustomerService $customers,
    ) {}

    public function index(Request $request): View|Response
    {
        $customersData = $this->customers->getPaginatedCustomers(15, $this->listFilters($request, [
            'search',
        ]));

        return $this->adminListResponse(
            $request,
            'v1.admin.customers.index',
            'v1.admin.customers.partials.results',
            ['customers' => $customersData],
            ['customers' => $customersData],
        );
    }

    public function create(): View
    {
        return view('v1.admin.customers.create');
    }

    public function store(StoreCustomerRequest $request): RedirectResponse
    {
        $this->customers->create($request->validated());

        return $this->redirectWithSuccess('v1.admin.customers.index', __('messages.Customer created successfully.'));
    }

    public function show(User $customer): View
    {
        // Add relationships needed for the view badges
        $customer->loadCount(['orders']);
        $customer->load([
            'addresses',
            'pointTransactions' => fn ($query) => $query->latest()->limit(50),
            'walletTransactions' => fn ($query) => $query->latest()->limit(20),
        ]);

        return view('v1.admin.customers.show', [
            'customer' => $customer,
        ]);
    }

    public function edit(User $customer): View
    {
        return view('v1.admin.customers.edit', [
            'customer' => $customer,
        ]);
    }

    public function update(UpdateCustomerRequest $request, User $customer): RedirectResponse
    {
        $this->customers->update($customer, $request->validated());

        return $this->redirectWithSuccess('v1.admin.customers.index', __('messages.Customer updated successfully.'));
    }

    public function destroy(User $customer): RedirectResponse
    {
        $this->customers->delete($customer);

        return $this->redirectWithSuccess('v1.admin.customers.index', __('messages.Customer deleted successfully.'));
    }
}
