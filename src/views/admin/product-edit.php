<?php
declare(strict_types=1);

$id = inputInt('id');
$product = $id ? fetchProductById($id) : null;
$isEdit = $product !== null;
$title = $isEdit ? 'Uredi proizvod' : 'Novi proizvod';

$brands = fetchBrands();
$categories = fetchCategories();
$allFlags = ['bestseller', 'new', 'sale', 'limited', 'exclusive'];
$productImages = $isEdit ? fetchProductImages($id) : [];
$productCategories = $isEdit ? array_column(fetchProductCategories($id), 'category_id') : [];
$productFlags = $isEdit ? array_column(fetchProductFlags($id), 'flag') : [];
$stockVariants = $isEdit ? fetchProductStock($id) : [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    requireCsrf();

    $data = [
        'name'             => inputString('name', '', $_POST),
        'slug'             => inputString('slug', '', $_POST) ?: generateSlug(inputString('name', '', $_POST)),
        'brand_id'         => inputInt('brand_id', 0, $_POST) ?: null,
        'description'      => trim($_POST['description'] ?? ''),
        'short_description'=> inputString('short_description', '', $_POST),
        'price'            => inputFloat('price', 0, $_POST),
        'sale_price'       => inputFloat('sale_price', 0, $_POST) ?: null,
        'meta_title'       => inputString('meta_title', '', $_POST),
        'meta_description' => inputString('meta_description', '', $_POST),
        'is_active'        => isset($_POST['is_active']) ? 1 : 0,
    ];

    $errors = [];
    if (!$data['name']) $errors[] = 'Naziv je obavezan.';
    if ($data['price'] <= 0) $errors[] = 'Cena mora biti veća od 0.';

    if (empty($errors)) {
        $savedId = saveProduct($data, $id ?: null);

        // Categories
        $catIds = array_map('intval', $_POST['categories'] ?? []);
        syncProductCategories($savedId, $catIds);

        // Flags
        $flags = array_intersect($_POST['flags'] ?? [], $allFlags);
        syncProductFlags($savedId, $flags);

        // Stock variants
        if (!empty($_POST['stock_label'])) {
            // Clear old stock
            $db = db();
            $db->prepare("DELETE FROM product_stock WHERE product_id = ?")->execute([$savedId]);
            foreach ($_POST['stock_label'] as $i => $label) {
                $label = trim($label);
                if ($label === '') continue;
                $qty = (int) ($_POST['stock_qty'][$i] ?? 0);
                $sku = trim($_POST['stock_sku'][$i] ?? '');
                updateProductStock($savedId, $label, $qty, $sku ?: null);
            }
        }

        // Image upload
        if (!empty($_FILES['images']['name'][0])) {
            foreach ($_FILES['images']['name'] as $i => $fname) {
                $file = [
                    'name'     => $_FILES['images']['name'][$i],
                    'type'     => $_FILES['images']['type'][$i],
                    'tmp_name' => $_FILES['images']['tmp_name'][$i],
                    'error'    => $_FILES['images']['error'][$i],
                    'size'     => $_FILES['images']['size'][$i],
                ];
                $result = uploadImage($file, 'uploads/products');
                if ($result['success']) {
                    $sortOrder = count($productImages) + $i;
                    addProductImage($savedId, $result['path'], $sortOrder);
                }
            }
        }

        flash('success', $isEdit ? 'Proizvod ažuriran.' : 'Proizvod kreiran.');
        redirect('/admin/product/edit?id=' . $savedId);
    } else {
        flash('error', implode(' ', $errors));
    }
}

require __DIR__ . '/../layout/admin-header.php';
?>

<div class="page-header">
    <a href="/admin/products" class="btn btn-secondary btn-sm">&larr; Nazad</a>
    <h1><?= $title ?></h1>
</div>

<form method="POST" enctype="multipart/form-data">
    <?= csrfField() ?>

    <div class="form-grid-2">
        <!-- Left Column -->
        <div>
            <div class="card mb-4">
                <h3>Osnovne informacije</h3>
                <div class="form-group">
                    <label>Naziv *</label>
                    <input type="text" name="name" class="form-control" value="<?= htmlspecialchars($product['name'] ?? '') ?>" required>
                </div>
                <div class="form-group">
                    <label>Slug</label>
                    <input type="text" name="slug" class="form-control" value="<?= htmlspecialchars($product['slug'] ?? '') ?>" placeholder="Automatski iz naziva">
                </div>
                <div class="form-group">
                    <label>Brend</label>
                    <select name="brand_id" class="form-control">
                        <option value="">-- Bez brenda --</option>
                        <?php foreach ($brands as $b): ?>
                        <option value="<?= $b['id'] ?>" <?= ($product['brand_id'] ?? '') == $b['id'] ? 'selected' : '' ?>><?= htmlspecialchars($b['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label>Kratak opis</label>
                    <input type="text" name="short_description" class="form-control" value="<?= htmlspecialchars($product['short_description'] ?? '') ?>">
                </div>
                <div class="form-group">
                    <label>Opis</label>
                    <textarea name="description" rows="6" class="form-control"><?= htmlspecialchars($product['description'] ?? '') ?></textarea>
                </div>
            </div>

            <div class="card mb-4">
                <h3>Cene</h3>
                <div class="form-row">
                    <div class="form-group">
                        <label>Cena (RSD) *</label>
                        <input type="number" name="price" class="form-control" step="0.01" value="<?= $product['price'] ?? '' ?>" required>
                    </div>
                    <div class="form-group">
                        <label>Akcijska cena (RSD)</label>
                        <input type="number" name="sale_price" class="form-control" step="0.01" value="<?= $product['sale_price'] ?? '' ?>">
                    </div>
                </div>
            </div>

            <div class="card mb-4">
                <h3>Varijante / Zalihe</h3>
                <div id="stock-container">
                    <?php if ($stockVariants): ?>
                        <?php foreach ($stockVariants as $sv): ?>
                        <div class="stock-row">
                            <input type="text" name="stock_label[]" class="form-control" placeholder="Npr: 250ml" value="<?= htmlspecialchars($sv['variant_label']) ?>">
                            <input type="number" name="stock_qty[]" class="form-control" placeholder="Količina" value="<?= (int) $sv['quantity'] ?>">
                            <input type="text" name="stock_sku[]" class="form-control" placeholder="SKU" value="<?= htmlspecialchars($sv['sku'] ?? '') ?>">
                            <button type="button" class="btn btn-sm btn-danger remove-stock">&times;</button>
                        </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="stock-row">
                            <input type="text" name="stock_label[]" class="form-control" placeholder="Npr: 250ml">
                            <input type="number" name="stock_qty[]" class="form-control" placeholder="Količina">
                            <input type="text" name="stock_sku[]" class="form-control" placeholder="SKU">
                            <button type="button" class="btn btn-sm btn-danger remove-stock">&times;</button>
                        </div>
                    <?php endif; ?>
                </div>
                <button type="button" class="btn btn-sm btn-secondary mt-2" id="add-stock-row">+ Dodaj varijantu</button>
            </div>
        </div>

        <!-- Right Column -->
        <div>
            <div class="card mb-4">
                <h3>Status i oznake</h3>
                <div class="form-group">
                    <label class="checkbox-label">
                        <input type="checkbox" name="is_active" value="1" <?= ($product['is_active'] ?? 1) ? 'checked' : '' ?>>
                        Aktivan
                    </label>
                </div>
                <div class="form-group">
                    <label>Oznake</label>
                    <?php foreach ($allFlags as $flag): ?>
                    <label class="checkbox-label">
                        <input type="checkbox" name="flags[]" value="<?= $flag ?>" <?= in_array($flag, $productFlags) ? 'checked' : '' ?>>
                        <?= ucfirst($flag) ?>
                    </label>
                    <?php endforeach; ?>
                </div>
            </div>

            <div class="card mb-4">
                <h3>Kategorije</h3>
                <div class="checkbox-list">
                    <?php foreach ($categories as $c): ?>
                    <label class="checkbox-label">
                        <input type="checkbox" name="categories[]" value="<?= $c['id'] ?>" <?= in_array($c['id'], $productCategories) ? 'checked' : '' ?>>
                        <?= htmlspecialchars($c['name']) ?>
                    </label>
                    <?php endforeach; ?>
                </div>
            </div>

            <div class="card mb-4">
                <h3>Slike</h3>
                <?php if ($productImages): ?>
                <div class="image-gallery">
                    <?php foreach ($productImages as $img): ?>
                    <div class="image-item">
                        <img src="<?= htmlspecialchars($img['image_url']) ?>" alt="">
                        <a href="/admin/product/delete-image?id=<?= $img['id'] ?>&product_id=<?= $id ?>" class="btn btn-sm btn-danger" onclick="return confirm('Obriši sliku?')">×</a>
                    </div>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>
                <div class="form-group">
                    <label>Dodaj slike</label>
                    <input type="file" name="images[]" multiple accept="image/*" class="form-control">
                </div>
            </div>

            <div class="card mb-4">
                <h3>SEO</h3>
                <div class="form-group">
                    <label>Meta naslov</label>
                    <input type="text" name="meta_title" class="form-control" value="<?= htmlspecialchars($product['meta_title'] ?? '') ?>">
                </div>
                <div class="form-group">
                    <label>Meta opis</label>
                    <textarea name="meta_description" rows="3" class="form-control"><?= htmlspecialchars($product['meta_description'] ?? '') ?></textarea>
                </div>
            </div>
        </div>
    </div>

    <div class="form-actions">
        <button type="submit" class="btn btn-primary"><?= $isEdit ? 'Sačuvaj izmene' : 'Kreiraj proizvod' ?></button>
        <a href="/admin/products" class="btn btn-secondary">Otkaži</a>
    </div>
</form>

<script>
document.getElementById('add-stock-row').addEventListener('click', function() {
    const c = document.getElementById('stock-container');
    const row = document.createElement('div');
    row.className = 'stock-row';
    row.innerHTML = '<input type="text" name="stock_label[]" class="form-control" placeholder="Npr: 250ml">'
        + '<input type="number" name="stock_qty[]" class="form-control" placeholder="Količina">'
        + '<input type="text" name="stock_sku[]" class="form-control" placeholder="SKU">'
        + '<button type="button" class="btn btn-sm btn-danger remove-stock">&times;</button>';
    c.appendChild(row);
});
document.getElementById('stock-container').addEventListener('click', function(e) {
    if (e.target.classList.contains('remove-stock')) {
        e.target.closest('.stock-row').remove();
    }
});
</script>

<?php require __DIR__ . '/../layout/admin-footer.php'; ?>
