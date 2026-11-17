<?php
class MpesaService {
    private array $c;
    public function __construct() { $this->c = config('mpesa', []); }
    private function sim(): bool { return (bool)($this->c['simulate'] ?? true); }

    public static function normalize(string $p): ?string {
        $p = preg_replace('/\D/', '', $p);
        if (preg_match('/^0([17]\d{8})$/', $p, $m)) $p = '254' . $m[1];
        return preg_match('/^254[17]\d{8}$/', $p) ? $p : null;
    }
    public function stkPush(string $phone, int $amount): array {
        $phone = self::normalize($phone);
        if (!$phone || $amount < 1) throw new InvalidArgumentException('Invalid phone number or amount.');
        if ($this->sim()) $id = 'SIM-' . bin2hex(random_bytes(6));
        else {
            $ts = date('YmdHis'); $c = $this->c;
            $r = $this->http('/mpesa/stkpush/v1/processrequest', [
                'BusinessShortCode' => $c['shortcode'], 'Password' => base64_encode($c['shortcode'] . $c['passkey'] . $ts), 'Timestamp' => $ts,
                'TransactionType' => 'CustomerPayBillOnline', 'Amount' => $amount, 'PartyA' => $phone, 'PartyB' => $c['shortcode'],
                'PhoneNumber' => $phone, 'CallBackURL' => $c['callback'], 'AccountReference' => 'POS', 'TransactionDesc' => 'POS sale']);
            if (empty($r['CheckoutRequestID'])) throw new RuntimeException('M-Pesa request failed: ' . ($r['errorMessage'] ?? 'unknown error'));
            $id = $r['CheckoutRequestID'];
        }
        q('INSERT INTO mpesa_requests(checkout_id,phone,amount) VALUES (?,?,?)', [$id, $phone, $amount]);
        return ['CheckoutRequestID' => $id];
    }
    public function status(string $id): array {
        $r = q('SELECT * FROM mpesa_requests WHERE checkout_id=?', [$id])->fetch();
        if (!$r) return ['status' => 'failed'];
        if ($this->sim() && $r['status'] === 'PENDING' && time() - strtotime($r['created_at']) >= 6) {   // simulated customer pays after ~6s
            $code = 'SIM' . strtoupper(bin2hex(random_bytes(4)));
            q("UPDATE mpesa_requests SET status='PAID', mpesa_code=? WHERE checkout_id=?", [$code, $id]);
            $r['status'] = 'PAID'; $r['mpesa_code'] = $code;
        }
        return ['status' => strtolower($r['status']), 'mpesa_code' => $r['mpesa_code']];
    }
    public function handleCallback(array $body): void {
        $cb = $body['Body']['stkCallback'] ?? null; if (!$cb) return;
        $id = $cb['CheckoutRequestID'] ?? '';
        if (($cb['ResultCode'] ?? 1) === 0) {
            $code = null; foreach ($cb['CallbackMetadata']['Item'] ?? [] as $i) if (($i['Name'] ?? '') === 'MpesaReceiptNumber') $code = $i['Value'];
            q("UPDATE mpesa_requests SET status='PAID', mpesa_code=? WHERE checkout_id=? AND status='PENDING'", [$code, $id]);
        } else q("UPDATE mpesa_requests SET status='FAILED' WHERE checkout_id=? AND status='PENDING'", [$id]);
    }
    private function http(string $path, array $body): array {
        $host = ($this->c['env'] ?? 'sandbox') === 'live' ? 'https://api.safaricom.co.ke' : 'https://sandbox.safaricom.co.ke';
        $tok = $this->token($host); $ch = curl_init();
        curl_setopt_array($ch, [CURLOPT_URL => $host . $path, CURLOPT_RETURNTRANSFER => true, CURLOPT_POST => true, CURLOPT_TIMEOUT => 20,
            CURLOPT_HTTPHEADER => ['Content-Type: application/json', "Authorization: Bearer $tok"], CURLOPT_POSTFIELDS => json_encode($body)]);
        $out = curl_exec($ch); curl_close($ch); return json_decode($out ?: '[]', true) ?: [];
    }
    private function token(string $host): string {
        $ch = curl_init("$host/oauth/v1/generate?grant_type=client_credentials");
        curl_setopt_array($ch, [CURLOPT_RETURNTRANSFER => true, CURLOPT_TIMEOUT => 20, CURLOPT_USERPWD => $this->c['key'] . ':' . $this->c['secret']]);
        $out = json_decode(curl_exec($ch) ?: '[]', true); curl_close($ch);
        return $out['access_token'] ?? throw new RuntimeException('Could not authenticate with M-Pesa.');
    }
}
