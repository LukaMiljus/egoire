<?php
declare(strict_types=1);
requireAdmin();

$dateFrom = inputString('date_from') ?: date('Y-m-01');
$dateTo = inputString('date_to') ?: date('Y-m-d');

$orders = analyticsExportOrders($dateFrom, $dateTo);

header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename="orders-' . $dateFrom . '-to-' . $dateTo . '.csv"');

$out = fopen('php://output', 'w');
fprintf($out, chr(0xEF) . chr(0xBB) . chr(0xBF)); // BOM for Excel

fputcsv($out, ['Br. porudžbine', 'Datum', 'Kupac', 'Email', 'Status', 'Plaćanje', 'Subtotal', 'Dostava', 'Poklon kartica', 'Ukupno']);

foreach ($orders as $o) {
    fputcsv($out, [
        $o['order_number'],
        $o['created_at'],
        $o['customer_name'],
        $o['email'],
        $o['status'],
        $o['payment_method'],
        $o['subtotal_price'],
        $o['shipping_price'],
        $o['gift_card_amount'] ?? 0,
        $o['total_price'],
    ]);
}

fclose($out);
exit;
