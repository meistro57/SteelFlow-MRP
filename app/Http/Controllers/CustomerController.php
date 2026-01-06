<?php

// app/Http/Controllers/CustomerController.php

namespace App\Http\Controllers;

use App\Http\Requests\ImportCustomersRequest;
use App\Http\Requests\StoreCustomerRequest;
use App\Http\Requests\UpdateCustomerRequest;
use App\Models\Customer;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

class CustomerController extends Controller
{
    /**
     * Display a listing of customers with optional search.
     */
    public function index(): Response
    {
        $query = Customer::query()->orderBy('name');

        if ($search = request('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('code', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        $customers = $query->paginate(20)->withQueryString();

        return Inertia::render('Customers/Index', [
            'customers' => $customers,
            'filters' => request()->only(['search']),
        ]);
    }

    /**
     * Show the form for creating a new customer.
     */
    public function create(): Response
    {
        return Inertia::render('Customers/Create');
    }

    /**
     * Store a newly created customer in storage.
     */
    public function store(StoreCustomerRequest $request): RedirectResponse
    {
        Customer::create($request->validated());

        return redirect()
            ->route('customers.index')
            ->with('success', 'Customer created successfully.');
    }

    /**
     * Show the form for editing the specified customer.
     */
    public function edit(Customer $customer): Response
    {
        return Inertia::render('Customers/Edit', [
            'customer' => $customer,
        ]);
    }

    /**
     * Update the specified customer in storage.
     */
    public function update(UpdateCustomerRequest $request, Customer $customer): RedirectResponse
    {
        $customer->update($request->validated());

        return redirect()
            ->route('customers.index')
            ->with('success', 'Customer updated successfully.');
    }

    /**
     * Remove the specified customer from storage when safe.
     */
    public function destroy(Customer $customer): RedirectResponse
    {
        if ($customer->projects()->exists()) {
            return redirect()
                ->back()
                ->with('error', 'Customer cannot be deleted while linked to projects.');
        }

        $customer->delete();

        return redirect()
            ->route('customers.index')
            ->with('success', 'Customer deleted successfully.');
    }

    /**
     * Display the import form for customers.
     */
    public function importForm(): Response
    {
        return Inertia::render('Customers/Import');
    }

    /**
     * Import customers from a CSV file.
     */
    public function import(ImportCustomersRequest $request): RedirectResponse
    {
        $file = $request->file('file');
        $handle = fopen($file->getRealPath(), 'rb');

        if ($handle === false) {
            return redirect()
                ->route('customers.import')
                ->with('error', 'Unable to read the uploaded file.');
        }

        $header = null;
        $created = 0;
        $updated = 0;
        $skipped = 0;

        while (($row = fgetcsv($handle, 0, ',')) !== false) {
            if ($header === null) {
                $header = array_map(fn ($value) => Str::slug(trim((string) $value), '_'), $row);

                continue;
            }

            if (count(array_filter($row, fn ($value) => $value !== null && $value !== '')) === 0) {
                continue; // Skip empty lines quietly.
            }

            $data = array_combine($header, $row);
            if ($data === false) {
                $skipped++;

                continue;
            }

            $payload = $this->mapRowToPayload($data);

            if (empty($payload['name'])) {
                $skipped++;

                continue;
            }

            $customer = $this->findExistingCustomer($payload);

            if ($customer) {
                $customer->fill($payload);
                $customer->save();
                $updated++;
            } else {
                Customer::create($payload);
                $created++;
            }
        }

        fclose($handle);

        $message = sprintf(
            'Import complete: %d added, %d updated, %d skipped.',
            $created,
            $updated,
            $skipped,
        );

        return redirect()
            ->route('customers.index')
            ->with('success', $message);
    }

    /**
     * Map a CSV row to fillable payload keys.
     */
    protected function mapRowToPayload(array $data): array
    {
        return [
            'code' => $this->cleanValue($data['code'] ?? null),
            'name' => $this->cleanValue($data['name'] ?? null),
            'address_1' => $this->cleanValue($data['address_1'] ?? null),
            'address_2' => $this->cleanValue($data['address_2'] ?? null),
            'city' => $this->cleanValue($data['city'] ?? null),
            'state' => $this->cleanValue($data['state'] ?? null),
            'zip' => $this->cleanValue($data['zip'] ?? null),
            'country' => $this->cleanValue($data['country'] ?? 'USA'),
            'phone' => $this->cleanValue($data['phone'] ?? null),
            'email' => $this->cleanValue($data['email'] ?? null),
            'notes' => $this->cleanValue($data['notes'] ?? null),
            'is_active' => $this->normalizeBoolean($data['is_active'] ?? true),
        ];
    }

    /**
     * Find an existing customer using code or email for matching.
     */
    protected function findExistingCustomer(array $payload): ?Customer
    {
        if (! empty($payload['code'])) {
            $customerByCode = Customer::where('code', $payload['code'])->first();
            if ($customerByCode) {
                return $customerByCode;
            }
        }

        if (! empty($payload['email'])) {
            return Customer::where('email', $payload['email'])->first();
        }

        return null;
    }

    /**
     * Clean incoming string values for safe storage.
     */
    protected function cleanValue(?string $value): ?string
    {
        $trimmed = trim((string) $value);

        return $trimmed === '' ? null : $trimmed;
    }

    /**
     * Normalize truthy and falsy import values.
     */
    protected function normalizeBoolean($value): bool
    {
        if (is_bool($value)) {
            return $value;
        }

        $normalized = strtolower(trim((string) $value));

        return ! in_array($normalized, ['0', 'false', 'no', 'n', 'inactive'], true);
    }
}
