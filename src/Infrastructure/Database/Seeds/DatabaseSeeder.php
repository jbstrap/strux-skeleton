<?php

namespace App\Infrastructure\Database\Seeds;

use App\Domain\Identity\Entity\Agent;
use App\Domain\Identity\Entity\Customer;
use App\Domain\Identity\Entity\Permission;
use App\Domain\Identity\Entity\Role;
use App\Domain\Identity\Entity\User;
use App\Domain\Identity\Enums\Permissions;

// Import Enum
use App\Domain\Ticketing\Entity\Department;
use App\Domain\Ticketing\Entity\Ticket;
use App\Domain\Ticketing\Entity\TicketAttachment;
use App\Domain\Ticketing\Entity\TicketComment;
use App\Domain\Ticketing\Entity\TicketPriority;
use App\Domain\Ticketing\Entity\TicketStatus;
use PDO;
use Strux\Component\Database\Seeder\SeederInterface;
use Strux\Support\Helpers\Utils;

class DatabaseSeeder implements SeederInterface
{
    private ?PDO $db = null;

    public function run(?PDO $db): void
    {
        $this->db = $db;
        echo "Starting Database Seeding via Models...\n";

        // 1. Create Roles & Permissions

        // Helper to create permission from Enum case
        $createPerm = function (Permissions $perm, string $name) {
            return $this->createPermission($name, $perm->value);
        };

        // User Permissions
        $pManageUsers = $createPerm(Permissions::MANAGE_USERS, 'Manage Users');
        $pViewUsers = $createPerm(Permissions::VIEW_USERS, 'View Users');
        $pImpersonate = $createPerm(Permissions::IMPERSONATE_USER, 'Impersonate User');
        $pDeleteUsers = $createPerm(Permissions::DELETE_USERS, 'Delete Users');
        $pViewAgents = $createPerm(Permissions::VIEW_AGENTS, 'View Agents');
        $pViewCustomers = $createPerm(Permissions::VIEW_CUSTOMERS, 'View Customers');

        // Ticket Permissions
        $pManageTickets = $createPerm(Permissions::MANAGE_TICKETS, 'Manage All Tickets');
        $pViewAllTickets = $createPerm(Permissions::VIEW_ALL_TICKETS, 'View All Tickets');
        $pViewAssigned = $createPerm(Permissions::VIEW_ASSIGNED_TICKETS, 'View Assigned Tickets');
        $pCreateTickets = $createPerm(Permissions::CREATE_TICKETS, 'Create Tickets');
        $pCommentTickets = $createPerm(Permissions::COMMENT_TICKETS, 'Comment on Tickets');
        $pAssignTickets = $createPerm(Permissions::ASSIGN_TICKETS, 'Assign Tickets');
        $pChangeStatus = $createPerm(Permissions::CHANGE_STATUS, 'Change Ticket Status');
        $pDeleteTickets = $createPerm(Permissions::DELETE_TICKETS, 'Delete Tickets');

        // Resources
        $pDownload = $createPerm(Permissions::DOWNLOAD_ATTACHMENTS, 'Download Attachments');
        $pManageDepts = $createPerm(Permissions::MANAGE_DEPARTMENTS, 'Manage Departments');
        $pManagePrio = $createPerm(Permissions::MANAGE_PRIORITIES, 'Manage Priorities');
        $pManageStatus = $createPerm(Permissions::MANAGE_STATUSES, 'Manage Statuses');

        // Analytics
        $pViewReports = $createPerm(Permissions::VIEW_REPORTS, 'View Reports');


        // Create Roles
        $roleAdmin = $this->createRole('Admin', 'admin', 'Administrator with full access');
        $roleAgent = $this->createRole('Agent', 'agent', 'Support Agent');
        $roleCustomer = $this->createRole('Customer', 'customer', 'Standard User');

        // --- Assign Permissions to Roles ---

        // ADMIN: Gets almost everything
        $adminPermissions = [
            $pManageUsers,
            $pViewUsers,
            $pImpersonate,
            $pDeleteUsers,
            $pViewAgents,
            $pViewCustomers,
            $pManageTickets,
            $pViewAllTickets,
            $pCreateTickets,
            $pCommentTickets,
            $pAssignTickets,
            $pChangeStatus,
            $pDeleteTickets,
            $pDownload,
            $pManageDepts,
            $pManagePrio,
            $pManageStatus,
            $pViewReports
        ];
        foreach ($adminPermissions as $p) $this->assignPermission($roleAdmin, $p);

        // AGENT: Focused on ticket handling
        $agentPermissions = [
            $pViewUsers, // To see customer profiles
            $pViewAllTickets, // Agents usually see the full queue
            $pViewAssigned,
            $pCreateTickets, // Can create tickets on behalf of users
            $pCommentTickets,
            $pAssignTickets, // Self-assign or assign to others (optional)
            $pChangeStatus,
            $pDownload
        ];
        foreach ($agentPermissions as $p) $this->assignPermission($roleAgent, $p);

        // CUSTOMER: Limited scope (usually handled by 'own resource' logic in Policy, but generic perms here)
        $customerPermissions = [
            $pCreateTickets,
            $pCommentTickets,
            $pDownload
            // View/Edit own tickets is typically logic-based, not just permission-based
        ];
        foreach ($customerPermissions as $p) $this->assignPermission($roleCustomer, $p);


        // 1. Create Departments
        $deptSupport = $this->createDepartment('Support');
        $deptTech = $this->createDepartment('Technical');
        $deptSales = $this->createDepartment('Sales');
        $deptBilling = $this->createDepartment('Billing');
        $deptFeatureRequest = $this->createDepartment('Feature Requests');

        // 2. Create Statuses
        $statusOpen = $this->createStatus('Open');
        $statusInProgress = $this->createStatus('In Progress');
        $statusClosed = $this->createStatus('Closed');
        $statusOnHold = $this->createStatus('On Hold');
        $statusResolved = $this->createStatus('Resolved');

        // 3. Create Priorities
        $priorityCritical = $this->createPriority('Critical');
        $priorityHigh = $this->createPriority('High');
        $priorityMedium = $this->createPriority('Medium');
        $priorityLow = $this->createPriority('Low');

        // 5. Create Users & Assign Roles
        // Admin
        $adminUser = $this->createUser('Jack', 'Smith', 'admin@mailinator.com', 'Admin');
        $this->assignRole($adminUser, $roleAdmin);

        // Agents
        $agentUser1 = $this->createUser('Jane', 'Walker', 'agent@mailinator.com', 'Agent');
        $this->assignRole($agentUser1, $roleAgent);
        $agent1 = $this->createAgent($agentUser1, 'Jane Walker', 'General Support, Technical', 'Online');

        $agentUser2 = $this->createUser('Alice', 'Brown', 'agent2@mailinator.com', 'Agent');
        $this->assignRole($agentUser2, $roleAgent);
        $agent2 = $this->createAgent($agentUser2, 'Alice Brown', 'Technical, Billing', 'Offline');

        $agentUser3 = $this->createUser('Mike', 'Johnson', 'mike.johnson@mailinator.com', 'Agent');
        $this->assignRole($agentUser3, $roleAgent);
        $agent3 = $this->createAgent($agentUser3, 'Mike Johnson', 'Sales, Support', 'Online');

        $agentUser4 = $this->createUser('Sarah', 'Chen', 'sarah.chen@mailinator.com', 'Agent');
        $this->assignRole($agentUser4, $roleAgent);
        $agent4 = $this->createAgent($agentUser4, 'Sarah Chen', 'Technical, Feature Requests', 'Away');

        // Customers
        $customerUser1 = $this->createUser('John', 'Doe', 'customer@mailinator.com', 'Customer');
        $this->assignRole($customerUser1, $roleCustomer);
        $customer1 = $this->createCustomer($customerUser1, 'John Doe', '012-093-1234', '123 Main St, New York, NY');

        $customerUser2 = $this->createUser('Bob', 'Green', 'customer2@mailinator.com', 'Customer');
        $this->assignRole($customerUser2, $roleCustomer);
        $customer2 = $this->createCustomer($customerUser2, 'Bob Green', '011-555-5678', '456 Oak Ave, Los Angeles, CA');

        $customerUser3 = $this->createUser('Emily', 'Wilson', 'emily.wilson@mailinator.com', 'Customer');
        $this->assignRole($customerUser3, $roleCustomer);
        $customer3 = $this->createCustomer($customerUser3, 'Emily Wilson', '013-444-9876', '789 Pine St, Chicago, IL');

        $customerUser4 = $this->createUser('David', 'Martinez', 'david.martinez@mailinator.com', 'Customer');
        $this->assignRole($customerUser4, $roleCustomer);
        $customer4 = $this->createCustomer($customerUser4, 'David Martinez', '014-333-2468', '321 Elm St, Miami, FL');

        $customerUser5 = $this->createUser('Lisa', 'Thompson', 'lisa.thompson@mailinator.com', 'Customer');
        $this->assignRole($customerUser5, $roleCustomer);
        $customer5 = $this->createCustomer($customerUser5, 'Lisa Thompson', '015-222-1357', '654 Birch Rd, Seattle, WA');

        // 5. Create Tickets & Interactions

        // Ticket 1 - Technical Issue
        $ticket1 = $this->createTicket($customer1, 'Cannot login to account', 'Forgot password link not working and getting 404 error', $statusOpen, $priorityHigh, $agent1, $deptTech);
        $this->addComment($ticket1, 'Customer', 'I am unable to login with my credentials. The forgot password page returns a 404 error.');
        $c1 = $this->addComment($ticket1, 'Agent', 'Hi John, can you please confirm you are using the latest password reset link from our main website?');
        $this->addComment($ticket1, 'Customer', 'Yes, I just tried it again but still get the 404 error. This is urgent as I need to access my account.');
        $c1_2 = $this->addComment($ticket1, 'Agent', 'Thanks for confirming. We found a misconfiguration in our password reset service. It should be fixed now. Please try again.');
        $this->addComment($ticket1, 'Customer', 'It works now! Thank you for the quick resolution.');

        $this->addAttachment($c1, 'screenshot1.png', '/uploads/screenshot1.png');
        $this->addAttachment($c1_2, 'fix_details.pdf', '/uploads/fix_details.pdf');

        // Ticket 2 - Critical Server Error
        $ticket2 = $this->createTicket($customer2, 'Error 500 on checkout page', 'Saving profile throws server error during payment process', $statusInProgress, $priorityCritical, $agent2, $deptTech);
        $c2 = $this->addComment($ticket2, 'Customer', 'The checkout page throws a 500 error when I try to complete my purchase. This is costing me business!');
        $this->addComment($ticket2, 'Agent', 'We are looking into the server logs for more detail. This appears to be a database connection issue.');
        $c2_2 = $this->addComment($ticket2, 'Agent', 'Found the issue - our database cluster had a node failure. We are failing over to backup nodes.');
        $this->addComment($ticket2, 'Agent', 'Service restored! The checkout should be working now. We apologize for the inconvenience.');

        $this->addAttachment($c2, 'error_log.txt', '/uploads/error_log.txt');
        $this->addAttachment($c2_2, 'db_failover_logs.txt', '/uploads/db_failover_logs.txt');

        // Ticket 3 - Feature Request
        $ticket3 = $this->createTicket($customer3, 'Dark mode feature request', 'Add dark mode toggle for better nighttime usage', $statusOpen, $priorityLow, $agent4, $deptFeatureRequest);
        $this->addComment($ticket3, 'Customer', 'Can you add a dark mode option? My eyes get strained using the bright interface at night.');
        $this->addComment($ticket3, 'Admin', 'Feature request noted and added to our product roadmap. We\'ll prioritize this in next sprint.');
        $c3 = $this->addComment($ticket3, 'Agent', 'Good news! Dark mode is now available under Settings → Theme. You can toggle between light and dark modes.');

        $this->addAttachment($c3, 'dark_mode_preview.jpg', '/uploads/dark_mode_preview.jpg');

        // Ticket 4 - Billing Issue
        $ticket4 = $this->createTicket($customer4, 'Double charged for monthly subscription', 'Payment processed twice for this month', $statusOpen, $priorityHigh, $agent2, $deptBilling);
        $this->addComment($ticket4, 'Customer', 'My payment was processed twice this month. I can see two identical charges on my credit card statement.');
        $c4 = $this->addComment($ticket4, 'Agent', 'I can confirm the double charge. We have initiated a refund for one of the payments. It should reflect in 3-5 business days.');
        $this->addComment($ticket4, 'Customer', 'Thank you for the quick response. Will wait for the refund.');

        $this->addAttachment($c4, 'refund_confirmation.pdf', '/uploads/refund_confirmation.pdf');

        // Ticket 5 - Sales Inquiry
        $ticket5 = $this->createTicket($customer5, 'Enterprise plan pricing', 'Interested in enterprise features and volume pricing', $statusOpen, $priorityMedium, $agent3, $deptSales);
        $this->addComment($ticket5, 'Customer', 'I would like to know more about your enterprise plan pricing and features for 100+ users.');
        $c5 = $this->addComment($ticket5, 'Agent', 'Thank you for your interest! I\'m sending you our enterprise pricing sheet and can schedule a demo if you\'d like.');
        $this->addComment($ticket5, 'Customer', 'The pricing looks good. Can we schedule a demo for next Tuesday?');

        $this->addAttachment($c5, 'enterprise_pricing.pdf', '/uploads/enterprise_pricing.pdf');

        // Ticket 6 - Technical Support (Unassigned)
        $ticket6 = $this->createTicket($customer1, 'Mobile src crashing on iOS', 'App crashes immediately after launch on iPhone 15', $statusOpen, $priorityHigh, null, $deptTech);
        $this->addComment($ticket6, 'Customer', 'The mobile src crashes as soon as I open it on my iPhone 15 Pro Max with iOS 17.');
        $this->addComment($ticket6, 'Agent', 'Ticket received and awaiting agent assignment.');

        // Ticket 7 - Account Issue
        $ticket7 = $this->createTicket($customer2, 'Cannot update profile picture', 'Upload fails with "file too large" error for small images', $statusInProgress, $priorityMedium, $agent1, $deptSupport);
        $c7 = $this->addComment($ticket7, 'Customer', 'I keep getting "file too large" error when trying to upload a 500KB profile picture. This seems like a bug.');
        $this->addComment($ticket7, 'Agent', 'We are investigating the file upload service. There might be a configuration issue with size limits.');

        $this->addAttachment($c7, 'profile_pic.jpg', '/uploads/profile_pic.jpg');

        // Ticket 8 - Feature Bug
        $ticket8 = $this->createTicket($customer3, 'Search function not working', 'Global search returns no results for existing content', $statusClosed, $priorityHigh, $agent4, $deptTech);
        $this->addComment($ticket8, 'Customer', 'The search function returns no results even for terms I know exist in my documents.');
        $this->addComment($ticket8, 'Agent', 'We found a bug in the search indexer. It was not updating properly with new content.');
        $this->addComment($ticket8, 'Agent', 'The search index has been rebuilt and should now return proper results. Please test and let us know.');
        $this->addComment($ticket8, 'Customer', 'Search is working perfectly now! Thank you.');

        // Ticket 9 - Billing Question
        $ticket9 = $this->createTicket($customer4, 'Invoice not received', 'Haven\'t received October invoice via email', $statusOnHold, $priorityMedium, $agent2, $deptBilling);
        $this->addComment($ticket9, 'Customer', 'I haven\'t received my October invoice via email. Can you resend it?');
        $this->addComment($ticket9, 'Agent', 'I\'ve resent the invoice to your registered email. Please check your spam folder as well.');

        // Ticket 10 - Performance Issue
        $ticket10 = $this->createTicket($customer5, 'Slow dashboard loading', 'Dashboard takes over 30 seconds to load', $statusInProgress, $priorityHigh, $agent3, $deptTech);
        $this->addComment($ticket10, 'Customer', 'The dashboard is extremely slow - takes 30+ seconds to load. This is affecting our team\'s productivity.');
        $c10 = $this->addComment($ticket10, 'Agent', 'We are analyzing the performance metrics. It appears to be a database query optimization issue.');
        $this->addComment($ticket10, 'Agent', 'We\'ve optimized the slow queries and added caching. Dashboard load time should be under 3 seconds now.');

        $this->addAttachment($c10, 'performance_metrics.png', '/uploads/performance_metrics.png');

        // Ticket 11 - Integration Request
        $ticket11 = $this->createTicket($customer1, 'Slack integration request', 'Need Slack notifications for team activities', $statusOpen, $priorityMedium, $agent4, $deptFeatureRequest);
        $this->addComment($ticket11, 'Customer', 'Can we get Slack integration to receive notifications for team activities?');
        $this->addComment($ticket11, 'Agent', 'Great suggestion! We have Slack integration on our development roadmap for Q2 next year.');

        // Ticket 12 - Security Concern
        $ticket12 = $this->createTicket($customer2, 'Suspicious login activity', 'Notifications about logins from unknown locations', $statusOpen, $priorityCritical, $agent1, $deptSupport);
        $this->addComment($ticket12, 'Customer', 'I received alerts about logins from China and Russia, but I\'m in the US. My account may be compromised.');
        $c12 = $this->addComment($ticket12, 'Agent', 'We\'ve immediately reset your password and logged out all sessions. Please set a new password and enable 2FA for added security.');

        $this->addAttachment($c12, 'security_protocol.pdf', '/uploads/security_protocol.pdf');

        echo "Database Seeding Completed Successfully.\n";
        echo "Created: 5 Departments, 5 Statuses, 4 Priorities\n";
        echo "Created: 1 Admin, 4 Agents, 5 Customers\n";
        echo "Created: 12 Tickets with various interactions and attachments\n";
    }

    // --- Helpers ---

    private function createRole(string $name, string $slug, string $desc): Role
    {
        $role = new Role();
        $role->name = $name;
        $role->slug = $slug;
        $role->description = $desc;
        $role->save();
        return $role;
    }

    private function createPermission(string $name, string $slug): Permission
    {
        $p = new Permission();
        $p->name = $name;
        $p->slug = $slug;
        $p->save();
        return $p;
    }

    private function assignRole(User $user, Role $role): void
    {
        // Manual Pivot Insert (accounts_roles)
        $sql = "INSERT INTO accounts_roles (roleID, userID) VALUES (?, ?)";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$role->roleID, $user->userID]);
    }

    private function assignPermission(Role $role, Permission $perm): void
    {
        // Manual Pivot Insert (permissions_roles)
        $sql = "INSERT INTO permissions_roles (permissionID, roleID) VALUES (?, ?)";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$perm->permissionID, $role->roleID]);
    }

    private function createUser(string $first, string $last, string $email, string $role): User
    {
        $user = new User();
        $user->firstname = $first;
        $user->lastname = $last;
        $user->email = $email;
        $user->setPassword('Pa$$w0rd!');
        $user->role = $role;
        $user->save();
        return $user;
    }

    private function createAgent(User $user, string $name, string $skill, string $availability): Agent
    {
        $agent = new Agent();
        $agent->userID = $user->userID;
        $agent->agentName = $name;
        $agent->skillset = $skill;
        $agent->availability = $availability;
        $agent->save();
        return $agent;
    }

    private function createCustomer(User $user, string $name, string $phone, string $address): Customer
    {
        $customer = new Customer();
        $customer->userID = $user->userID;
        $customer->customerName = $name;
        $customer->phone = $phone;
        $customer->address = $address;
        $customer->save();
        return $customer;
    }

    private function createDepartment(string $name): Department
    {
        $dept = new Department();
        $dept->departmentName = $name;
        $dept->save();
        return $dept;
    }

    private function createStatus(string $name): TicketStatus
    {
        $status = new TicketStatus();
        $status->statusName = $name;
        $status->save();
        return $status;
    }

    private function createPriority(string $name): TicketPriority
    {
        $p = new TicketPriority();
        $p->priorityName = $name;
        $p->save();
        return $p;
    }

    private function createTicket(Customer $customer, string $subject, string $desc, TicketStatus $status, TicketPriority $prio, ?Agent $agent, Department $dept): Ticket
    {
        $ticket = new Ticket();
        $ticket->ticketID = Utils::generateId(upperChars: false, lowerChars: false);
        $ticket->customerID = $customer->customerID;
        $ticket->subject = $subject;
        $ticket->description = $desc;
        $ticket->statusID = $status->statusID;
        $ticket->priorityID = $prio->priorityID;
        $ticket->assignedTo = $agent?->agentID;
        $ticket->departmentID = $dept->departmentID;
        $ticket->save();
        return $ticket;
    }

    private function addComment(Ticket $ticket, string $role, string $msg): TicketComment
    {
        $comment = new TicketComment();
        $comment->ticketID = $ticket->ticketID;
        $comment->authorRole = $role;
        $comment->message = $msg;
        $comment->save();

        if (!$ticket->comments->isEmpty()) {
            $ticket->comments->add($comment);
        }

        return $comment;
    }

    private function addAttachment(TicketComment $comment, string $name, string $path): void
    {
        $att = new TicketAttachment();
        $att->commentID = $comment->commentID;
        $att->fileName = $name;
        $att->filePath = $path;
        $att->save();

        if (!$comment->attachments->isEmpty()) {
            $comment->attachments->add($att);
        }
    }
}