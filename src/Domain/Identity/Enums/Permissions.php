<?php

declare(strict_types=1);

namespace App\Domain\Identity\Enums;

/**
 * Enum representing various permissions within the application.
 */

/**
 * MANAGE_USERS — create/edit/delete users, change roles
 * VIEW_USERS — view user list and profiles
 * MANAGE_TICKETS — full ticket CRUD for all tickets
 * VIEW_ALL_TICKETS — view any ticket
 * VIEW_ASSIGNED_TICKETS — view tickets assigned to you (agent)
 * CREATE_TICKETS — create a ticket (customer or agent on behalf)
 * COMMENT_TICKETS — post replies / comments
 * ASSIGN_TICKETS — assign tickets to an agent
 * CHANGE_STATUS — change ticket status (Open, In Progress, Closed)
 * DOWNLOAD_ATTACHMENTS — download attachments
 * MANAGE_DEPARTMENTS / MANAGE_PRIORITIES / MANAGE_STATUSES — config
 * VIEW_REPORTS — access analytics / exports
 * IMPERSONATE_USER — admin-only impersonation
 * DELETE_TICKETS — (restrict to Admin by default)
 * DELETE_USERS — Admin-only
 */
enum Permissions: string
{
    // User Management
    case MANAGE_USERS = 'manage_users';
    case VIEW_USERS = 'view_users';
    case IMPERSONATE_USER = 'impersonate_user';
    case DELETE_USERS = 'delete_users';
    case VIEW_AGENTS = 'view_agents';
    case VIEW_CUSTOMERS = 'view_customers';

    // Ticket Management
    case MANAGE_TICKETS = 'manage_tickets'; // Full CRUD
    case VIEW_ALL_TICKETS = 'view_all_tickets';
    case VIEW_ASSIGNED_TICKETS = 'view_assigned_tickets';
    case CREATE_TICKETS = 'create_tickets';
    case COMMENT_TICKETS = 'comment_tickets';
    case ASSIGN_TICKETS = 'assign_tickets';
    case CHANGE_STATUS = 'change_status';
    case DELETE_TICKETS = 'delete_tickets';

    // Resources & Config
    case DOWNLOAD_ATTACHMENTS = 'download_attachments';
    case MANAGE_DEPARTMENTS = 'manage_departments';
    case MANAGE_PRIORITIES = 'manage_priorities';
    case MANAGE_STATUSES = 'manage_statuses';

    // Analytics
    case VIEW_REPORTS = 'view_reports';
}