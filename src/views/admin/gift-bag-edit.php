<?php
declare(strict_types=1);

$id = inputInt('id');
$rule = $id ? fetchGiftBagRuleById($id) : null;
$isEdit = $rule !== null;
$title = $isEdit ? 'Uredi Gift Bag pravilo' : 'Novo Gift Bag pravilo';
$discounts = $isEdit ? fetchGiftBagDiscounts($id) : [];
$categories = fetchCategories();
$brands = fetchBrands();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    requireCsrf();
    $data = [
        'name'             => inputString('name', '', $_POST),
        'min_order_amount' => inputFloat('min_order_amount', 0, $_POST),
        'discount_type'    => inputString('discount_type', 'percentage', $_POST),
        'discount_value'   => inputFloat('discount_value', 0, $_POST),
        'applies_to'       => inputString('applies_to', 'cheapest', $_POST),
        'conditions'       => json_encode([
            'categories' => array_map('intval', $_POST['condition_categories'] ?? []),
            'brands'     => array_map('intval', $_POST['condition_brands'] ?? []),
            'min_items'  => inputInt('min_items', 0, $_POST),
        ]),
        'is_active'        => isset($_POST['is_active']) ? 1 : 0,
        'start_date'       => inputString('start_date', '', $_POST) ?: null,
        'end_date'         => inputString('end_date', '', $_POST) ?: null,
    ];

    $errors = [];
    if (!$data['name']) $errors[] = 'Naziv je obavezan.';

    if (empty($errors)) {
        $savedId = saveGiftBagRule($data, $id ?: null);

        // Save tier discounts
        $db = db();
        $db->prepare("DELETE FROM gift_bag_discounts WHERE gift_bag_rule_id = ?")->execute([$savedId]);
        if (!empty($_POST['tier_min'])) {
            foreach ($_POST['tier_min'] as $i => $min) {
                $tierMin = (float) $min;
                $tierDisc = (float) ($_POST['tier_discount'][$i] ?? 0);
                if ($tierMin > 0 && $tierDisc > 0) {
                    $db->prepare("INSERT INTO gift_bag_discounts (gift_bag_rule_id, min_amount, discount_percent) VALUES (?, ?, ?)")
                       ->execute([$savedId, $tierMin, $tierDisc]);
                }
            }
        }

        flash('success', $isEdit ? 'Pravilo ažurirano.' : 'Pravilo kreirano.');
        redirect('/admin/gift-bag');
    } else {
        flash('error', implode(' ', $errors));
    }
}

require __DIR__ . '/../layout/admin-header.php';
?>

<div class="page-header">
    <a href="/admin/gift-bag" class="btn btn-secondary btn-sm">&larr; Nazad</a>
    <h1><?= $title ?></h1>
</div>

<form method="POST">
    <?= csrfField() ?>
    <div class="form-grid-2">
        <div>
            <div class="card mb-4">
                <h3>Osnovno</h3>
                <div class="form-group">
                    <label>Naziv *</label>
                    <input type="text" name="name" class="form-control" value="<?= htmlspecialchars($rule['name'] ?? '') ?>" required>
                </div>
                <div class="form-group">
                    <label>Min iznos narudžbine (RSD)</label>
                    <input type="number" step="0.01" name="min_order_amount" class="form-control" value="<?= $rule['min_order_amount'] ?? 0 ?>">
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label>Tip popusta</label>
                        <select name="discount_type" class="form-control">
                            <option value="percentage" <?= ($rule['discount_type'] ?? '') === 'percentage' ? 'selected' : '' ?>>Procenat</option>
                            <option value="fixed" <?= ($rule['discount_type'] ?? '') === 'fixed' ? 'selected' : '' ?>>Fiksni iznos</option>
                            <option value="free_product" <?= ($rule['discount_type'] ?? '') === 'free_product' ? 'selected' : '' ?>>Besplatan proizvod</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Vrednost</label>
                        <input type="number" step="0.01" name="discount_value" class="form-control" value="<?= $rule['discount_value'] ?? 0 ?>">
                    </div>
                </div>
                <div class="form-group">
                    <label>Primenjuje se na</label>
                    <select name="applies_to" class="form-control">
                        <option value="cheapest" <?= ($rule['applies_to'] ?? '') === 'cheapest' ? 'selected' : '' ?>>Najjeftiniji proizvod</option>
                        <option value="all" <?= ($rule['applies_to'] ?? '') === 'all' ? 'selected' : '' ?>>Sve stavke</option>
                        <option value="specific" <?= ($rule['applies_to'] ?? '') === 'specific' ? 'selected' : '' ?>>Specifično</option>
                    </select>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label>Datum početka</label>
                        <input type="date" name="start_date" class="form-control" value="<?= $rule['start_date'] ?? '' ?>">
                    </div>
                    <div class="form-group">
                        <label>Datum kraja</label>
                        <input type="date" name="end_date" class="form-control" value="<?= $rule['end_date'] ?? '' ?>">
                    </div>
                </div>
                <div class="form-group">
                    <label class="checkbox-label">
                        <input type="checkbox" name="is_active" value="1" <?= ($rule['is_active'] ?? 1) ? 'checked' : '' ?>>
                        Aktivno
                    </label>
                </div>
            </div>
        </div>

        <div>
            <div class="card mb-4">
                <h3>Uslovi</h3>
                <div class="form-group">
                    <label>Min stavki u korpi</label>
                    <input type="number" name="min_items" class="form-control" value="<?= json_decode($rule['conditions'] ?? '{}', true)['min_items'] ?? 0 ?>">
                </div>
                <div class="form-group">
                    <label>Kategorije</label>
                    <?php
                    $condCats = json_decode($rule['conditions'] ?? '{}', true)['categories'] ?? [];
                    foreach ($categories as $c): ?>
                    <label class="checkbox-label">
                        <input type="checkbox" name="condition_categories[]" value="<?= $c['id'] ?>" <?= in_array($c['id'], $condCats) ? 'checked' : '' ?>>
                        <?= htmlspecialchars($c['name']) ?>
                    </label>
                    <?php endforeach; ?>
                </div>
                <div class="form-group">
                    <label>Brendovi</label>
                    <?php
                    $condBrands = json_decode($rule['conditions'] ?? '{}', true)['brands'] ?? [];
                    foreach ($brands as $b): ?>
                    <label class="checkbox-label">
                        <input type="checkbox" name="condition_brands[]" value="<?= $b['id'] ?>" <?= in_array($b['id'], $condBrands) ? 'checked' : '' ?>>
                        <?= htmlspecialchars($b['name']) ?>
                    </label>
                    <?php endforeach; ?>
                </div>
            </div>

            <div class="card mb-4">
                <h3>Tier popusti</h3>
                <div id="tier-container">
                    <?php if ($discounts): ?>
                        <?php foreach ($discounts as $d): ?>
                        <div class="stock-row">
                            <input type="number" step="0.01" name="tier_min[]" class="form-control" placeholder="Min iznos" value="<?= $d['min_amount'] ?>">
                            <input type="number" step="0.01" name="tier_discount[]" class="form-control" placeholder="Popust %" value="<?= $d['discount_percent'] ?>">
                            <button type="button" class="btn btn-sm btn-danger remove-tier">&times;</button>
                        </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="stock-row">
                            <input type="number" step="0.01" name="tier_min[]" class="form-control" placeholder="Min iznos">
                            <input type="number" step="0.01" name="tier_discount[]" class="form-control" placeholder="Popust %">
                            <button type="button" class="btn btn-sm btn-danger remove-tier">&times;</button>
                        </div>
                    <?php endif; ?>
                </div>
                <button type="button" class="btn btn-sm btn-secondary mt-2" id="add-tier-row">+ Dodaj tier</button>
            </div>
        </div>
    </div>

    <div class="form-actions">
        <button type="submit" class="btn btn-primary"><?= $isEdit ? 'Sačuvaj' : 'Kreiraj' ?></button>
        <a href="/admin/gift-bag" class="btn btn-secondary">Otkaži</a>
    </div>
</form>

<script>
document.getElementById('add-tier-row').addEventListener('click', function() {
    const c = document.getElementById('tier-container');
    const row = document.createElement('div');
    row.className = 'stock-row';
    row.innerHTML = '<input type="number" step="0.01" name="tier_min[]" class="form-control" placeholder="Min iznos">'
        + '<input type="number" step="0.01" name="tier_discount[]" class="form-control" placeholder="Popust %">'
        + '<button type="button" class="btn btn-sm btn-danger remove-tier">&times;</button>';
    c.appendChild(row);
});
document.getElementById('tier-container').addEventListener('click', function(e) {
    if (e.target.classList.contains('remove-tier')) e.target.closest('.stock-row').remove();
});
</script>

<?php require __DIR__ . '/../layout/admin-footer.php'; ?>
