<?php
class SaleController {
    public function checkout(): void { Auth::requireLogin(); render('pos/checkout'); }
    public function findProduct(string $code): void {
        Auth::requireLogin(); $p = (new Product())->findByCode(trim($code));
        $p ? json_out($p) : json_out(['error' => 'Product not found'], 404);
    }
    public function createSale(): void {
        Auth::requireLogin(); $in = json_in();
        try { json_out((new CheckoutService())->process(Auth::user()['id'], $in['cart'] ?? [], $in['payment'] ?? [], (float)($in['discount_pct'] ?? 0))); }
        catch (Throwable $e) { json_out(['error' => $e->getMessage()], 422); }
    }
    public function sendMpesaPrompt(): void {
        Auth::requireLogin(); $in = json_in();
        try { json_out((new MpesaService())->stkPush((string)($in['phone'] ?? ''), (int)($in['amount'] ?? 0))); }
        catch (Throwable $e) { json_out(['error' => $e->getMessage()], 422); }
    }
    public function mpesaStatus(): void { Auth::requireLogin(); json_out((new MpesaService())->status((string)($_GET['id'] ?? ''))); }
    public function mpesaCallback(): void {   // called by Safaricom: no session, no CSRF
        (new MpesaService())->handleCallback(json_in());
        json_out(['ResultCode' => 0, 'ResultDesc' => 'Accepted']);
    }
}
