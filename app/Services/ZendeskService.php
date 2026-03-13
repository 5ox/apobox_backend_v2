<?php

namespace App\Services;

use App\Models\Customer;
use App\Models\Order;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Exception;

class ZendeskService
{
    protected string $subdomain;
    protected string $apiToken;
    protected string $agentEmail;

    public function __construct(string $subdomain, string $apiToken, string $agentEmail)
    {
        $this->subdomain = $subdomain;
        $this->apiToken = $apiToken;
        $this->agentEmail = $agentEmail;
    }

    /**
     * Get the Zendesk log channel.
     */
    protected function log(): \Psr\Log\LoggerInterface
    {
        return Log::channel('zendesk');
    }

    /**
     * Check if Zendesk credentials are configured.
     */
    public function isConfigured(): bool
    {
        return !empty($this->apiToken) && !empty($this->subdomain) && !empty($this->agentEmail);
    }

    /**
     * Base URL for the Zendesk API.
     */
    protected function baseUrl(): string
    {
        return "https://{$this->subdomain}.zendesk.com/api/v2";
    }

    /**
     * Build an authenticated HTTP client.
     */
    protected function client()
    {
        return Http::withBasicAuth("{$this->agentEmail}/token", $this->apiToken)
            ->acceptJson()
            ->asJson()
            ->timeout(15);
    }

    /**
     * Create a Zendesk ticket for a problem order.
     */
    public function createTicketForOrder(Order $order, string $reason, string $comments = ''): ?array
    {
        if (!$this->isConfigured()) {
            return null;
        }

        $order->loadMissing('customer');
        $customer = $order->customer;

        $billingId = $customer?->billing_id ?? 'N/A';
        $customerName = trim(($customer?->customers_firstname ?? '') . ' ' . ($customer?->customers_lastname ?? ''));
        $customerEmail = $customer?->customers_email_address;

        if (empty($customerEmail)) {
            $this->log()->warning('Zendesk: cannot create ticket — no customer email', [
                'orders_id' => $order->orders_id,
            ]);
            return null;
        }

        $daysInWarehouse = $order->date_purchased
            ? (int) $order->date_purchased->diffInDays(today())
            : 0;

        $body = implode("\n", array_filter([
            "Order #{$order->orders_id}",
            "Customer: {$customerName} ({$billingId})",
            "Email: {$customerEmail}",
            "Date Purchased: " . ($order->date_purchased?->format('m/d/Y') ?? 'N/A'),
            "Days in Warehouse: {$daysInWarehouse}",
            "",
            "Problem Reason: {$reason}",
            $comments ? "Comments: {$comments}" : null,
        ]));

        $tag = Str::slug($reason, '_');

        $payload = [
            'ticket' => [
                'subject' => "Order #{$order->orders_id} — {$reason}",
                'comment' => ['body' => $body],
                'requester' => [
                    'name' => $customerName ?: $billingId,
                    'email' => $customerEmail,
                ],
                'priority' => 'normal',
                'tags' => ['problem', $tag],
            ],
        ];

        try {
            $response = $this->client()->post("{$this->baseUrl()}/tickets.json", $payload);

            if ($response->successful()) {
                $ticket = $response->json('ticket');
                $ticketId = $ticket['id'] ?? null;

                $this->log()->info('Zendesk: ticket created', [
                    'orders_id' => $order->orders_id,
                    'ticket_id' => $ticketId,
                ]);

                return [
                    'ticket_id' => $ticketId,
                    'ticket_url' => "https://{$this->subdomain}.zendesk.com/agent/tickets/{$ticketId}",
                ];
            }

            $errorBody = $response->json();
            $errorMsg = $errorBody['error'] ?? $errorBody['description'] ?? json_encode($errorBody);
            $this->log()->error('Zendesk: ticket creation failed', [
                'orders_id' => $order->orders_id,
                'status' => $response->status(),
                'body' => $errorBody,
            ]);

            return ['error' => "Zendesk API {$response->status()}: {$errorMsg}"];
        } catch (Exception $e) {
            $this->log()->error('Zendesk: ticket creation exception', [
                'orders_id' => $order->orders_id,
                'error' => $e->getMessage(),
            ]);
            report($e);
            return ['error' => $e->getMessage()];
        }
    }

    /**
     * Create a general Zendesk ticket for a customer (not order-specific).
     */
    public function createTicketForCustomer(Customer $customer, string $subject, string $description): ?array
    {
        if (!$this->isConfigured()) {
            return null;
        }

        $customerName = trim(($customer->customers_firstname ?? '') . ' ' . ($customer->customers_lastname ?? ''));
        $customerEmail = $customer->customers_email_address;

        if (empty($customerEmail)) {
            $this->log()->warning('Zendesk: cannot create ticket — no customer email', [
                'customers_id' => $customer->customers_id,
            ]);
            return null;
        }

        $billingId = $customer->billing_id ?? 'N/A';

        $body = implode("\n", [
            "Customer: {$customerName} ({$billingId})",
            "Email: {$customerEmail}",
            "",
            $description,
        ]);

        $payload = [
            'ticket' => [
                'subject' => $subject,
                'comment' => ['body' => $body],
                'requester' => [
                    'name' => $customerName ?: $billingId,
                    'email' => $customerEmail,
                ],
                'priority' => 'normal',
                'tags' => ['customer_support'],
            ],
        ];

        try {
            $response = $this->client()->post("{$this->baseUrl()}/tickets.json", $payload);

            if ($response->successful()) {
                $ticket = $response->json('ticket');
                $ticketId = $ticket['id'] ?? null;

                $this->log()->info('Zendesk: customer ticket created', [
                    'customers_id' => $customer->customers_id,
                    'ticket_id' => $ticketId,
                ]);

                return [
                    'ticket_id' => $ticketId,
                    'ticket_url' => "https://{$this->subdomain}.zendesk.com/agent/tickets/{$ticketId}",
                ];
            }

            $errorBody = $response->json();
            $errorMsg = $errorBody['error'] ?? $errorBody['description'] ?? json_encode($errorBody);
            $this->log()->error('Zendesk: customer ticket creation failed', [
                'customers_id' => $customer->customers_id,
                'status' => $response->status(),
                'body' => $errorBody,
            ]);

            return ['error' => "Zendesk API {$response->status()}: {$errorMsg}"];
        } catch (Exception $e) {
            $this->log()->error('Zendesk: customer ticket creation exception', [
                'customers_id' => $customer->customers_id,
                'error' => $e->getMessage(),
            ]);
            report($e);
            return ['error' => $e->getMessage()];
        }
    }

    /**
     * Search for tickets by requester email address.
     */
    public function getTicketsForEmail(string $email): array
    {
        if (!$this->isConfigured()) {
            return [];
        }

        try {
            $response = $this->client()->get("{$this->baseUrl()}/search.json", [
                'query' => "type:ticket requester:{$email}",
                'sort_by' => 'updated_at',
                'sort_order' => 'desc',
            ]);

            if (!$response->successful()) {
                $this->log()->warning('Zendesk: search failed', [
                    'email' => $email,
                    'status' => $response->status(),
                ]);
                return [];
            }

            $results = $response->json('results', []);

            return collect($results)->map(fn($t) => [
                'id' => $t['id'],
                'subject' => $t['subject'] ?? '',
                'status' => $t['status'] ?? 'unknown',
                'priority' => $t['priority'] ?? null,
                'created_at' => $t['created_at'] ?? null,
                'updated_at' => $t['updated_at'] ?? null,
                'url' => "https://{$this->subdomain}.zendesk.com/agent/tickets/{$t['id']}",
            ])->all();
        } catch (Exception $e) {
            $this->log()->error('Zendesk: search exception', [
                'email' => $email,
                'error' => $e->getMessage(),
            ]);
            return [];
        }
    }

    /**
     * Fetch a single ticket by ID.
     */
    public function getTicket(int $ticketId): ?array
    {
        if (!$this->isConfigured()) {
            return null;
        }

        try {
            $response = $this->client()->get("{$this->baseUrl()}/tickets/{$ticketId}.json");

            if (!$response->successful()) {
                return null;
            }

            $t = $response->json('ticket');

            return [
                'id' => $t['id'],
                'subject' => $t['subject'] ?? '',
                'status' => $t['status'] ?? 'unknown',
                'priority' => $t['priority'] ?? null,
                'created_at' => $t['created_at'] ?? null,
                'updated_at' => $t['updated_at'] ?? null,
                'url' => "https://{$this->subdomain}.zendesk.com/agent/tickets/{$t['id']}",
            ];
        } catch (Exception $e) {
            $this->log()->error('Zendesk: getTicket exception', [
                'ticket_id' => $ticketId,
                'error' => $e->getMessage(),
            ]);
            return null;
        }
    }
}
