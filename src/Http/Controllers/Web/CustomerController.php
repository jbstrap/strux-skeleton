<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web;

use App\Domain\Identity\Entity\Customer;
use App\Domain\Identity\Entity\User;
use Strux\Auth\Middleware\AuthorizationMiddleware;
use Strux\Component\Attributes\{Prefix};
use Strux\Component\Attributes\Middleware;
use Strux\Component\Attributes\Route;
use Strux\Component\Http\Controller\Web\Controller;
use Strux\Component\Http\Request;
use Strux\Component\Http\Response;
use Strux\Component\Validation\Rules\Email;
use Strux\Component\Validation\Rules\MinLength;
use Strux\Component\Validation\Rules\Required;
use Strux\Component\Validation\Validator;

/**
 * @property Customer $model - For model auto-completion.
 */
#[Prefix('/admin/customers')]
#[Middleware([AuthorizationMiddleware::class])]
class CustomerController extends Controller
{
    #[Route('/', methods: ['GET'], name: 'admin.customers.index')]
    public function index(Request $request): Response
    {
        // Eager load the user information with each customer to avoid N+1 queries.
        $customers = Customer::query()->with('user')->get();

        return $this->view('admin/customers/index', [
            'title' => 'Manage Customers',
            'customers' => $customers,
        ]);
    }

    #[Route('/create', methods: ['GET'])]
    public function create(): Response
    {
        return $this->view('admin/customers/create', [
            'title' => 'Create Customer'
        ]);
    }

    #[Route('/store', methods: ['POST'])]
    public function store(Request $request): Response
    {
        $validator = new Validator($request->allPost());
        $validator->add('firstName', [new Required(), new MinLength(2)]);
        $validator->add('lastName', [new Required(), new MinLength(2)]);
        $validator->add('email', [new Required(), new Email()]);
        // Add more validation as needed...

        if (!$validator->isValid()) {
            flash()->set('errors', $validator->getErrors());
            flash()->set('old', $request->allPost());
            return $this->redirect($this->route('customers.create'));
        }

        // In a real application, you would wrap this in a database transaction.
        // $this->db->beginTransaction();

        try {
            // Check if user already exists
            if (User::query()->where('email', $request->input('email')->string())->first()) {
                throw new \Exception('A user with this email address already exists.');
            }

            // Create the User account first
            $user = new User();
            $user->firstname = $request->input('firstName')->safe()->string();
            $user->lastname = $request->input('lastName')->safe()->string();
            $user->email = $request->input('email')->safe()->string();
//            $user->role = 'Customer';
            // $user->registerDate = date('Y-m-d');
            $user->setPassword('password'); // In a real src, you'd generate or require a password
            $user->save();

            // Create the associated Customer profile
            $customer = new Customer();
            $customer->userID = $user->userID;
            $customer->customerName = $user->firstName . ' ' . $user->lastName;
            $customer->phone = $request->input('phone')->safe()->string();
            $customer->address = $request->input('address')->safe()->string();
            $customer->save();

            // $this->db->commit();
            flash()->set('success', 'Customer created successfully.');

        } catch (\Exception $e) {
            // $this->db->rollback();
            flash()->set('error', 'Failed to create customer: ' . $e->getMessage());
            return $this->redirect($this->route('customers.create'));
        }

        return $this->redirect($this->route('customers.index'));
    }

    #[Route('/edit/:id', methods: ['GET', 'POST'])]
    public function edit(int $id): Response
    {
        $customer = Customer::query()->with('user')->find($id);

        if (!$customer) {
            flash()->set('error', 'Customer not found.');
            return $this->redirect($this->route('customers.index'));
        }

        return $this->view('admin/customers/edit', [
            'title' => 'Edit Customer',
            'customer' => $customer,
        ]);
    }

    #[Route('/update/:id', methods: ['GET', 'POST'])]
    public function update(Request $request, int $id): Response
    {
        $customer = Customer::find($id);
        if (!$customer) {
            flash()->set('error', 'Customer not found.');
            return $this->redirect($this->route('customers.index'));
        }

        // ... (validation logic similar to store) ...

        $customer->customerName = $request->input('customerName')->safe()->string();
        $customer->phone = $request->input('phone')->safe()->string();
        $customer->save();

        flash()->set('success', 'Customer updated successfully.');
        return $this->redirect($this->route('customers.index'));
    }

    #[Route('/delete/:id', methods: ['DELETE'])]
    public function destroy(int $id): Response
    {
        // The foreign key in the database is set to ON DELETE CASCADE,
        // so deleting the User will automatically delete the associated Customer.
        if (User::destroy($id)) {
            flash()->set('success', 'Customer and associated user account deleted successfully.');
        } else {
            flash()->set('error', 'Could not delete customer.');
        }

        return $this->redirect($this->route('customers.index'));
    }
}
