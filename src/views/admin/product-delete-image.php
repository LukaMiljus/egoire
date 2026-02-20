<?php
declare(strict_types=1);

$imageId = inputInt('id');
$productId = inputInt('product_id');

if ($imageId && $productId) {
    // Fetch image to get file path before deleting
    $stmt = db()->prepare('SELECT * FROM product_images WHERE id = ? AND product_id = ?');
    $stmt->execute([$imageId, $productId]);
    $image = $stmt->fetch();

    if ($image) {
        // Delete from database
        deleteProductImage($imageId);

        // Delete physical file if it exists
        $filePath = __DIR__ . '/../../public' . $image['image_path'];
        if (file_exists($filePath)) {
            @unlink($filePath);
        }

        flash('success', 'Slika obrisana.');
    } else {
        flash('error', 'Slika nije pronađena.');
    }
} else {
    flash('error', 'Nevažeći parametri.');
}

redirect('/admin/product/edit?id=' . $productId);
