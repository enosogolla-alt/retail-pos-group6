<?php

declare(strict_types=1);

/**
 * Simulated M-Pesa Express (STK Push) payment prompts.
 *
 * The prompt is confirmed a few seconds after it is sent, with a generated M-Pesa code.
 * A phone number ending in 0000 simulates a customer cancelling the prompt.
 *
 * Owner: Winston Enos (M4)
 * Tasks: 4.4
 */
final class MpesaService
{
    private const SESSION_KEY = 'mpesa_requests';
    private const SIMULATED_DELAY_SECONDS = 4;

    /**
     * @return array{checkout_request_id: string, message: string}
     */
    public function stkPush(string $phone, string $amount): array
    {
        $phone = trim($phone);
        if (preg_match('/^0[17]\d{8}$/', $phone) !== 1) {
            throw new DomainException('Enter a valid Safaricom number, for example 0712345678.');
        }
        if ((float) $amount <= 0) {
            throw new DomainException('The amount must be more than 0.');
        }

        $requestId = 'ws_CO_' . bin2hex(random_bytes(8));
        $_SESSION[self::SESSION_KEY][$requestId] = [
            'phone' => $phone,
            'amount' => $amount,
            'sent_at' => time(),
            'mpesa_code' => null,
        ];

        return [
            'checkout_request_id' => $requestId,
            'message' => 'Prompt sent to ' . substr($phone, 0, 4) . ' ' . substr($phone, 4, 3) . ' ' . substr($phone, 7) . '.',
        ];
    }

    /**
     * @return array{status: string, mpesa_code: ?string, message: string}
     */
    public function status(string $checkoutRequestId): array
    {
        $request = $_SESSION[self::SESSION_KEY][$checkoutRequestId] ?? null;
        if ($request === null) {
            return ['status' => 'UNKNOWN', 'mpesa_code' => null, 'message' => 'Payment request not found. Send the prompt again.'];
        }

        if (time() - $request['sent_at'] < self::SIMULATED_DELAY_SECONDS) {
            return ['status' => 'PENDING', 'mpesa_code' => null, 'message' => 'Waiting for the customer to enter their PIN.'];
        }

        if (str_ends_with($request['phone'], '0000')) {
            return ['status' => 'FAILED', 'mpesa_code' => null, 'message' => 'The customer cancelled the M-Pesa prompt.'];
        }

        if ($request['mpesa_code'] === null) {
            $request['mpesa_code'] = $this->newCode();
            $_SESSION[self::SESSION_KEY][$checkoutRequestId] = $request;
        }

        return ['status' => 'PAID', 'mpesa_code' => $request['mpesa_code'], 'message' => 'Payment received.'];
    }

    /**
     * Records the result Safaricom posts to /mpesa/callback.
     */
    public function handleCallback(array $payload): void
    {
        $result = $payload['Body']['stkCallback'] ?? [];

        AuditLogger::log(null, 'MPESA_CALLBACK', sprintf(
            '%s · result %s · %s',
            (string) ($result['CheckoutRequestID'] ?? 'unknown request'),
            (string) ($result['ResultCode'] ?? '?'),
            (string) ($result['ResultDesc'] ?? '')
        ));
    }

    private function newCode(): string
    {
        $letters = 'ABCDEFGHJKLMNPQRSTUVWXYZ';
        $characters = $letters . '0123456789';

        do {
            $code = $letters[random_int(0, strlen($letters) - 1)];
            for ($i = 0; $i < 9; $i++) {
                $code .= $characters[random_int(0, strlen($characters) - 1)];
            }
        } while (Payment::mpesaCodeExists($code));

        return $code;
    }
}
