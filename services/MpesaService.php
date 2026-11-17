<?php

declare(strict_types=1);

/**
 * M-Pesa Express (STK Push) through the Safaricom Daraja API.
 *
 * Owner: Winston Enos (M4)
 * Tasks: 4.4
 */
final class MpesaService
{
    /**
     * Sends a payment prompt to the customer's phone.
     *
     * @return array{checkout_request_id: string}
     */
    public function stkPush(string $phone, string $amount, string $reference): array
    {
        throw new LogicException('Not implemented: task 4.4.');
    }

    /**
     * @return array{status: string, mpesa_code: ?string}
     */
    public function status(string $checkoutRequestId): array
    {
        throw new LogicException('Not implemented: task 4.4.');
    }

    /**
     * Records the result Safaricom posts to /mpesa/callback.
     *
     * @param array<string, mixed> $payload
     */
    public function handleCallback(array $payload): void
    {
        throw new LogicException('Not implemented: task 4.4.');
    }
}
