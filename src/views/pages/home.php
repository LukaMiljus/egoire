<?php
/* ============================================================
   Egoire – Luxury Homepage
   View:  src/views/pages/home.php
   ============================================================ */
declare(strict_types=1);

$title = 'Egoire | Luxury Hair Care';

/* --- Page-specific assets --- */
$pageStyles  = ['/css/home/home.css'];
$pageScripts = ['/js/home.js'];

/* --- Data --- */
$bestSellers  = fetchProducts(['flags' => ['best_seller'], 'active' => true, 'limit' => 6]);
$editorsPick  = fetchProducts(['active' => true, 'limit' => 6, 'sort' => 'newest']);
$saleProducts = fetchProducts(['on_sale' => true, 'active' => true, 'limit' => 6]);
$newProducts  = fetchProducts(['flags' => ['new'], 'active' => true, 'limit' => 3]);
$blogPosts    = fetchBlogPosts(['limit' => 3, 'published_only' => true]);
$giftBagRules    = fetchGiftBagRules(true);
$giftCardAmounts = fetchGiftCardAmounts();
$brands          = fetchBrands(['active_only' => true]);
$parentCategories = fetchCategories(['parent_id' => null, 'active_only' => true]);

/* Static testimonials (no DB table) */
$testimonials = [
    [
        'text'   => 'Egoire proizvodi su potpuno transformisali moju kosu. Posle samo mesec dana korišćenja, rezultati su neverovatni — sjaj, mekoća i zdravlje koje nisam imala godinama.',
        'author' => 'Marija S.',
        'role'   => 'Redovna kupac',
    ],
    [
        'text'   => 'Konačno sam pronašla brend koji razume premium negu kose. Svaki proizvod koji sam probala je premašio moja očekivanja. Apsolutna preporuka!',
        'author' => 'Jelena D.',
        'role'   => 'Profesionalni frizer',
    ],
    [
        'text'   => 'Poklon kutija za moju sestru je bila savršena — prelepo upakovana, luksuzni proizvodi i brza isporuka. Egoire je definicija elegancije.',
        'author' => 'Nikola M.',
        'role'   => 'Zadovoljan kupac',
    ],
    [
        'text'   => 'Kao profesionalka u beauty industriji, biram samo najbolje za svoje klijente. Egoire je jedini brend kome verujem za negu kose — kvalitet je besprekoran.',
        'author' => 'Ana V.',
        'role'   => 'Beauty bloger',
    ],
];

require __DIR__ . '/../layout/header.php';
?>

<!-- ============================================================
     1. HERO  –  85 vh desktop · 70 vh mobile
     ============================================================ -->
<section class="eh-hero" data-reveal>

    <video class="eh-hero__media"
           autoplay
           muted
           loop
           playsinline
           preload="metadata"
           aria-hidden="true">
        <source src="/images/banner/hero-video.mp4" type="video/mp4">
    </video>

    <div class="eh-hero__overlay"></div>

    <div class="eh-hero__content">
        <span class="eh-hero__eyebrow" data-reveal data-delay="200">Ekskluzivna kolekcija</span>
        <img class="eh-hero__logo" src="/public/images/logos/egoire-logo.png" alt="Egoire Logo" srcset="">
        <h1 class="eh-hero__title" data-reveal data-delay="400">Luxury Hair Care</h1>
        <p class="eh-hero__subtitle" data-reveal data-delay="600">
            Premium proizvodi svetskih brendova za transformaciju i negu vaše kose
        </p>
        <div class="eh-hero__cta" data-reveal data-delay="800">
            <a href="/products" class="eh-btn eh-btn--primary">Pogledaj proizvode</a>
            <a href="/brands" class="eh-btn eh-btn--ghost">Naši brendovi</a>
        </div>
    </div>

    <!-- Scroll hint -->
    <div class="eh-hero__scroll" data-reveal data-delay="1200">
        <span>Otkrijte više</span>
        <svg width="16" height="24" viewBox="0 0 16 24" fill="none" stroke="currentColor" stroke-width="1.5">
            <rect x="1" y="1" width="14" height="22" rx="7"/>
            <line x1="8" y1="6" x2="8" y2="10" class="eh-hero__scroll-dot"/>
        </svg>
    </div>
</section>

<!-- ============================================================
     2. FEATURED PRODUCTS  –  Two sliders
     ============================================================ -->

<?php if ($bestSellers): ?>
<section class="eh-section" data-reveal>
    <div class="eh-container">
        <header class="eh-section-header">
            <span class="eh-section-header__eyebrow">Najtraženije</span>
            <h2 class="eh-section-header__title">Najprodavaniji proizvodi</h2>
            <p class="eh-section-header__desc">Otkrijte zašto hiljade kupaca bira upravo ove proizvode</p>
        </header>

        <div class="eh-slider" data-slider>
            <div class="eh-slider__track">
                <?php foreach ($bestSellers as $p):
                    $imgs = fetchProductImages((int) $p['id']);
                    $flags = fetchProductFlags((int) $p['id']);
                ?>
                <div class="eh-slider__slide">
                    <article class="eh-card" data-product-id="<?= (int) $p['id'] ?>">
                        <div class="eh-card__visual">
                            <a href="/product/<?= htmlspecialchars($p['slug']) ?>" class="eh-card__img-link" tabindex="-1">
                                <?php if (!empty($imgs)): ?>
                                <img src="<?= htmlspecialchars($imgs[0]['image_path']) ?>"
                                     alt="<?= htmlspecialchars($p['name']) ?>" loading="lazy">
                                <?php else: ?>
                                <div class="eh-card__placeholder">Egoire</div>
                                <?php endif; ?>
                            </a>
                            <?php if ($flags): ?>
                            <div class="eh-card__badges">
                                <?php foreach ($flags as $f): ?>
                                <span class="eh-badge eh-badge--<?= $f ?>"><?= ucfirst(str_replace('_', ' ', $f)) ?></span>
                                <?php endforeach; ?>
                            </div>
                            <?php endif; ?>
                        </div>
                        <div class="eh-card__body">
                            <span class="eh-card__brand"><?= htmlspecialchars($p['brand_name'] ?? '') ?></span>
                            <h3 class="eh-card__name">
                                <a href="/product/<?= htmlspecialchars($p['slug']) ?>"><?= htmlspecialchars($p['name']) ?></a>
                            </h3>
                            <?php if ($p['short_description']): ?>
                            <p class="eh-card__desc"><?= htmlspecialchars(truncate($p['short_description'], 80)) ?></p>
                            <?php endif; ?>
                            <div class="eh-card__price">
                                <?php if ($p['sale_price']): ?>
                                <span class="eh-card__price-old"><?= formatPrice((float) $p['price']) ?></span>
                                <span class="eh-card__price-sale"><?= formatPrice((float) $p['sale_price']) ?></span>
                                <?php else: ?>
                                <span class="eh-card__price-current"><?= formatPrice((float) $p['price']) ?></span>
                                <?php endif; ?>
                            </div>
                            <div class="eh-card__actions">
                                <div class="eh-stepper">
                                    <button class="eh-stepper__btn" type="button" data-action="minus" aria-label="Smanji količinu">−</button>
                                    <input  class="eh-stepper__input" type="number" value="1" min="1" max="99" aria-label="Količina">
                                    <button class="eh-stepper__btn" type="button" data-action="plus" aria-label="Povećaj količinu">+</button>
                                </div>
                                <button class="eh-card__add-btn" type="button">
                                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M6 2L3 6v14a2 2 0 002 2h14a2 2 0 002-2V6l-3-4z"/><line x1="3" y1="6" x2="21" y2="6"/><path d="M16 10a4 4 0 01-8 0"/></svg>
                                    <span>Dodaj u korpu</span>
                                </button>
                            </div>
                        </div>
                    </article>
                </div>
                <?php endforeach; ?>
            </div>
            <div class="eh-slider__nav">
                <button class="eh-slider__arrow eh-slider__arrow--prev" aria-label="Prethodni">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="15 18 9 12 15 6"/></svg>
                </button>
                <div class="eh-slider__dots"></div>
                <button class="eh-slider__arrow eh-slider__arrow--next" aria-label="Sledeći">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="9 18 15 12 9 6"/></svg>
                </button>
            </div>
        </div>
    </div>
</section>
<?php endif; ?>

<?php if ($editorsPick): ?>
<section class="eh-section eh-section--alt" data-reveal>
    <div class="eh-container">
        <header class="eh-section-header">
            <span class="eh-section-header__eyebrow">Naša preporuka</span>
            <h2 class="eh-section-header__title">Naš izbor za vas</h2>
            <p class="eh-section-header__desc">Pažljivo odabrani proizvodi od strane naših stručnjaka</p>
        </header>

        <div class="eh-slider" data-slider>
            <div class="eh-slider__track">
                <?php foreach ($editorsPick as $p):
                    $imgs = fetchProductImages((int) $p['id']);
                    $flags = fetchProductFlags((int) $p['id']);
                ?>
                <div class="eh-slider__slide">
                    <article class="eh-card" data-product-id="<?= (int) $p['id'] ?>">
                        <div class="eh-card__visual">
                            <a href="/product/<?= htmlspecialchars($p['slug']) ?>" class="eh-card__img-link" tabindex="-1">
                                <?php if (!empty($imgs)): ?>
                                <img src="<?= htmlspecialchars($imgs[0]['image_path']) ?>"
                                     alt="<?= htmlspecialchars($p['name']) ?>" loading="lazy">
                                <?php else: ?>
                                <div class="eh-card__placeholder">Egoire</div>
                                <?php endif; ?>
                            </a>
                            <?php if ($flags): ?>
                            <div class="eh-card__badges">
                                <?php foreach ($flags as $f): ?>
                                <span class="eh-badge eh-badge--<?= $f ?>"><?= ucfirst(str_replace('_', ' ', $f)) ?></span>
                                <?php endforeach; ?>
                            </div>
                            <?php endif; ?>
                        </div>
                        <div class="eh-card__body">
                            <span class="eh-card__brand"><?= htmlspecialchars($p['brand_name'] ?? '') ?></span>
                            <h3 class="eh-card__name">
                                <a href="/product/<?= htmlspecialchars($p['slug']) ?>"><?= htmlspecialchars($p['name']) ?></a>
                            </h3>
                            <?php if ($p['short_description']): ?>
                            <p class="eh-card__desc"><?= htmlspecialchars(truncate($p['short_description'], 80)) ?></p>
                            <?php endif; ?>
                            <div class="eh-card__price">
                                <?php if ($p['sale_price']): ?>
                                <span class="eh-card__price-old"><?= formatPrice((float) $p['price']) ?></span>
                                <span class="eh-card__price-sale"><?= formatPrice((float) $p['sale_price']) ?></span>
                                <?php else: ?>
                                <span class="eh-card__price-current"><?= formatPrice((float) $p['price']) ?></span>
                                <?php endif; ?>
                            </div>
                            <div class="eh-card__actions">
                                <div class="eh-stepper">
                                    <button class="eh-stepper__btn" type="button" data-action="minus" aria-label="Smanji količinu">−</button>
                                    <input  class="eh-stepper__input" type="number" value="1" min="1" max="99" aria-label="Količina">
                                    <button class="eh-stepper__btn" type="button" data-action="plus" aria-label="Povećaj količinu">+</button>
                                </div>
                                <button class="eh-card__add-btn" type="button">
                                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M6 2L3 6v14a2 2 0 002 2h14a2 2 0 002-2V6l-3-4z"/><line x1="3" y1="6" x2="21" y2="6"/><path d="M16 10a4 4 0 01-8 0"/></svg>
                                    <span>Dodaj u korpu</span>
                                </button>
                            </div>
                        </div>
                    </article>
                </div>
                <?php endforeach; ?>
            </div>
            <div class="eh-slider__nav">
                <button class="eh-slider__arrow eh-slider__arrow--prev" aria-label="Prethodni">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="15 18 9 12 15 6"/></svg>
                </button>
                <div class="eh-slider__dots"></div>
                <button class="eh-slider__arrow eh-slider__arrow--next" aria-label="Sledeći">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="9 18 15 12 9 6"/></svg>
                </button>
            </div>
        </div>
    </div>
</section>
<?php endif; ?>

<!-- ============================================================
     2b. BRANDS  –  Logo marquee / grid from DB
     ============================================================ -->

<?php if ($brands): ?>
<section class="eh-section eh-section--brands" data-reveal>
    <div class="eh-container">
        <header class="eh-section-header">
            <span class="eh-section-header__eyebrow">Premium selekcija</span>
            <h2 class="eh-section-header__title">Naši brendovi</h2>
            <p class="eh-section-header__desc">Svetski poznati brendovi odabrani za izuzetnu negu vaše kose</p>
        </header>

        <div class="eh-brands">
            <?php foreach ($brands as $index => $brand): ?>
            <a href="/brand/<?= htmlspecialchars($brand['slug']) ?>"
               class="eh-brands__item"
               data-reveal data-delay="<?= $index * 80 ?>">
                <div class="eh-brands__logo-wrap">
                    <?php if (!empty($brand['logo'])): ?>
                    <img src="<?= htmlspecialchars($brand['logo']) ?>"
                         alt="<?= htmlspecialchars($brand['name']) ?>"
                         class="eh-brands__logo"
                         loading="lazy">
                    <?php else: ?>
                    <span class="eh-brands__name-fallback"><?= htmlspecialchars($brand['name']) ?></span>
                    <?php endif; ?>
                </div>
                <span class="eh-brands__label"><?= htmlspecialchars($brand['name']) ?></span>
            </a>
            <?php endforeach; ?>
        </div>

        <div class="eh-section-footer">
            <a href="/brands" class="eh-btn eh-btn--outline">Pogledaj sve brendove</a>
        </div>
    </div>
</section>
<?php endif; ?>

<!-- ============================================================
     3. SALE SECTION  –  Conditional
     ============================================================ -->

<?php if ($saleProducts): ?>
<section class="eh-section eh-section--sale" data-reveal>
    <div class="eh-container">
        <header class="eh-section-header">
            <span class="eh-section-header__eyebrow">Posebna ponuda</span>
            <h2 class="eh-section-header__title">Proizvodi na akciji</h2>
            <p class="eh-section-header__desc">Iskoristite ekskluzivne popuste na odabrane premium proizvode</p>
        </header>

        <div class="eh-slider" data-slider>
            <div class="eh-slider__track">
                <?php foreach ($saleProducts as $p):
                    $imgs = fetchProductImages((int) $p['id']);
                ?>
                <div class="eh-slider__slide">
                    <article class="eh-card eh-card--sale" data-product-id="<?= (int) $p['id'] ?>">
                        <div class="eh-card__visual">
                            <a href="/product/<?= htmlspecialchars($p['slug']) ?>" class="eh-card__img-link" tabindex="-1">
                                <?php if (!empty($imgs)): ?>
                                <img src="<?= htmlspecialchars($imgs[0]['image_path']) ?>"
                                     alt="<?= htmlspecialchars($p['name']) ?>" loading="lazy">
                                <?php else: ?>
                                <div class="eh-card__placeholder">Egoire</div>
                                <?php endif; ?>
                            </a>
                            <div class="eh-card__badges">
                                <span class="eh-badge eh-badge--sale">Akcija</span>
                            </div>
                        </div>
                        <div class="eh-card__body">
                            <span class="eh-card__brand"><?= htmlspecialchars($p['brand_name'] ?? '') ?></span>
                            <h3 class="eh-card__name">
                                <a href="/product/<?= htmlspecialchars($p['slug']) ?>"><?= htmlspecialchars($p['name']) ?></a>
                            </h3>
                            <?php if ($p['short_description']): ?>
                            <p class="eh-card__desc"><?= htmlspecialchars(truncate($p['short_description'], 80)) ?></p>
                            <?php endif; ?>
                            <div class="eh-card__price">
                                <span class="eh-card__price-old"><?= formatPrice((float) $p['price']) ?></span>
                                <span class="eh-card__price-sale"><?= formatPrice((float) $p['sale_price']) ?></span>
                            </div>
                            <div class="eh-card__actions">
                                <div class="eh-stepper">
                                    <button class="eh-stepper__btn" type="button" data-action="minus" aria-label="Smanji količinu">−</button>
                                    <input  class="eh-stepper__input" type="number" value="1" min="1" max="99" aria-label="Količina">
                                    <button class="eh-stepper__btn" type="button" data-action="plus" aria-label="Povećaj količinu">+</button>
                                </div>
                                <button class="eh-card__add-btn" type="button">
                                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M6 2L3 6v14a2 2 0 002 2h14a2 2 0 002-2V6l-3-4z"/><line x1="3" y1="6" x2="21" y2="6"/><path d="M16 10a4 4 0 01-8 0"/></svg>
                                    <span>Dodaj u korpu</span>
                                </button>
                            </div>
                        </div>
                    </article>
                </div>
                <?php endforeach; ?>
            </div>
            <div class="eh-slider__nav">
                <button class="eh-slider__arrow eh-slider__arrow--prev" aria-label="Prethodni">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="15 18 9 12 15 6"/></svg>
                </button>
                <div class="eh-slider__dots"></div>
                <button class="eh-slider__arrow eh-slider__arrow--next" aria-label="Sledeći">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="9 18 15 12 9 6"/></svg>
                </button>
            </div>
        </div>
    </div>
</section>
<?php endif; ?>

<!-- ============================================================
     4. NEW PRODUCTS  –  3-column grid
     ============================================================ -->

<?php if ($newProducts): ?>
<section class="eh-section" data-reveal>
    <div class="eh-container">
        <header class="eh-section-header">
            <span class="eh-section-header__eyebrow">Sveže u ponudi</span>
            <h2 class="eh-section-header__title">Novo u kolekciji</h2>
            <p class="eh-section-header__desc">Poslednji dodaci našoj ekskluzivnoj ponudi</p>
        </header>

        <div class="eh-grid eh-grid--3">
            <?php foreach ($newProducts as $index => $p):
                $imgs = fetchProductImages((int) $p['id']);
                $flags = fetchProductFlags((int) $p['id']);
            ?>
            <article class="eh-card" data-product-id="<?= (int) $p['id'] ?>" data-reveal data-delay="<?= $index * 150 ?>">
                <div class="eh-card__visual">
                    <a href="/product/<?= htmlspecialchars($p['slug']) ?>" class="eh-card__img-link" tabindex="-1">
                        <?php if (!empty($imgs)): ?>
                        <img src="<?= htmlspecialchars($imgs[0]['image_path']) ?>"
                             alt="<?= htmlspecialchars($p['name']) ?>" loading="lazy">
                        <?php else: ?>
                        <div class="eh-card__placeholder">Egoire</div>
                        <?php endif; ?>
                    </a>
                    <?php if ($flags): ?>
                    <div class="eh-card__badges">
                        <?php foreach ($flags as $f): ?>
                        <span class="eh-badge eh-badge--<?= $f ?>"><?= ucfirst(str_replace('_', ' ', $f)) ?></span>
                        <?php endforeach; ?>
                    </div>
                    <?php endif; ?>
                </div>
                <div class="eh-card__body">
                    <span class="eh-card__brand"><?= htmlspecialchars($p['brand_name'] ?? '') ?></span>
                    <h3 class="eh-card__name">
                        <a href="/product/<?= htmlspecialchars($p['slug']) ?>"><?= htmlspecialchars($p['name']) ?></a>
                    </h3>
                    <?php if ($p['short_description']): ?>
                    <p class="eh-card__desc"><?= htmlspecialchars(truncate($p['short_description'], 80)) ?></p>
                    <?php endif; ?>
                    <div class="eh-card__price">
                        <?php if ($p['sale_price']): ?>
                        <span class="eh-card__price-old"><?= formatPrice((float) $p['price']) ?></span>
                        <span class="eh-card__price-sale"><?= formatPrice((float) $p['sale_price']) ?></span>
                        <?php else: ?>
                        <span class="eh-card__price-current"><?= formatPrice((float) $p['price']) ?></span>
                        <?php endif; ?>
                    </div>
                    <div class="eh-card__actions">
                        <div class="eh-stepper">
                            <button class="eh-stepper__btn" type="button" data-action="minus" aria-label="Smanji količinu">−</button>
                            <input  class="eh-stepper__input" type="number" value="1" min="1" max="99" aria-label="Količina">
                            <button class="eh-stepper__btn" type="button" data-action="plus" aria-label="Povećaj količinu">+</button>
                        </div>
                        <button class="eh-card__add-btn" type="button">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M6 2L3 6v14a2 2 0 002 2h14a2 2 0 002-2V6l-3-4z"/><line x1="3" y1="6" x2="21" y2="6"/><path d="M16 10a4 4 0 01-8 0"/></svg>
                            <span>Dodaj u korpu</span>
                        </button>
                    </div>
                </div>
            </article>
            <?php endforeach; ?>
        </div>

        <div class="eh-section-footer">
            <a href="/products?sort=newest" class="eh-btn eh-btn--outline">Pogledaj sve novitete</a>
        </div>
    </div>
</section>
<?php endif; ?>

<!-- ============================================================
     4b. CATEGORIES  –  Hierarchical (parents + subcategories)
     ============================================================ -->

<?php if ($parentCategories): ?>
<section class="eh-section eh-section--categories" data-reveal>
    <div class="eh-container">
        <header class="eh-section-header">
            <span class="eh-section-header__eyebrow">Naša ponuda</span>
            <h2 class="eh-section-header__title">Kategorije proizvoda</h2>
            <p class="eh-section-header__desc">Pronađite savršene proizvode prema tipu nege</p>
        </header>

        <div class="eh-categories">
            <?php foreach ($parentCategories as $pIndex => $parentCat):
                $subcats = fetchSubcategories((int) $parentCat['id']);
            ?>
            <div class="eh-categories__group" data-reveal data-delay="<?= $pIndex * 150 ?>">
                <!-- Parent category card -->
                <a href="/category/<?= htmlspecialchars($parentCat['slug']) ?>" class="eh-categories__parent">
                    <div class="eh-categories__parent-img">
                        <?php if (!empty($parentCat['image'])): ?>
                        <img src="<?= htmlspecialchars($parentCat['image']) ?>"
                             alt="<?= htmlspecialchars($parentCat['name']) ?>"
                             loading="lazy">
                        <?php else: ?>
                        <div class="eh-categories__parent-placeholder">
                            <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                                <path d="M20.59 13.41l-7.17 7.17a2 2 0 01-2.83 0L2 12V2h10l8.59 8.59a2 2 0 010 2.82z"/>
                                <line x1="7" y1="7" x2="7.01" y2="7"/>
                            </svg>
                        </div>
                        <?php endif; ?>
                        <div class="eh-categories__parent-overlay">
                            <span class="eh-categories__parent-name"><?= htmlspecialchars($parentCat['name']) ?></span>
                        </div>
                    </div>
                </a>

                <!-- Subcategories -->
                <?php if ($subcats): ?>
                <div class="eh-categories__children">
                    <?php foreach ($subcats as $sub): ?>
                    <a href="/category/<?= htmlspecialchars($sub['slug']) ?>" class="eh-categories__child">
                        <?php if (!empty($sub['image'])): ?>
                        <img src="<?= htmlspecialchars($sub['image']) ?>"
                             alt="<?= htmlspecialchars($sub['name']) ?>"
                             class="eh-categories__child-img"
                             loading="lazy">
                        <?php else: ?>
                        <span class="eh-categories__child-icon">✦</span>
                        <?php endif; ?>
                        <span class="eh-categories__child-name"><?= htmlspecialchars($sub['name']) ?></span>
                    </a>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>
            </div>
            <?php endforeach; ?>
        </div>

        <div class="eh-section-footer">
            <a href="/categories" class="eh-btn eh-btn--outline">Sve kategorije</a>
        </div>
    </div>
</section>
<?php endif; ?>

<!-- ============================================================
     5. GIFT BAG  –  Two-column split
     ============================================================ -->

<section class="eh-section eh-section--gift-bag" data-reveal>
    <div class="eh-container">
        <div class="eh-split">
            <div class="eh-split__media" data-reveal data-delay="0">
                <div class="eh-split__img-wrap">
                   <img src="/images/gift-bag/gift-bag2.png" alt="gift bag">
                </div>
            </div>
            <div class="eh-split__content" data-reveal data-delay="200">
                <span class="eh-section-header__eyebrow">Specijalna ponuda</span>
                <h2 class="eh-section-header__title">Gift Bag</h2>
                <p class="eh-split__text">
                    Otkrijte naše ekskluzivne Gift Bag ponude — posebno kreirane kombinacije
                    premium proizvoda po neodoljivim cenama. Savršen poklon za voljenu osobu
                    ili za sebe.
                </p>
                <?php if ($giftBagRules): ?>
                <div class="eh-split__highlights">
                    <?php foreach (array_slice($giftBagRules, 0, 2) as $rule): ?>
                    <div class="eh-highlight">
                        <span class="eh-highlight__icon">✦</span>
                        <span class="eh-highlight__text"><?= htmlspecialchars($rule['name']) ?></span>
                    </div>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>
                <a href="/gift-bag" class="eh-btn eh-btn--primary">Istraži Gift Bag</a>
            </div>
        </div>
    </div>
</section>

<!-- ============================================================
     6. GIFT CARDS  –  3-column
     ============================================================ -->

<?php if ($giftCardAmounts): ?>
<section class="eh-section eh-section--alt" data-reveal>
    <div class="eh-container">
        <header class="eh-section-header">
            <span class="eh-section-header__eyebrow">Savršen poklon</span>
            <h2 class="eh-section-header__title">Poklon kartice</h2>
            <p class="eh-section-header__desc">Poklonite luksuznu negu kose — izaberite iznos koji vam odgovara</p>
        </header>

        <div class="eh-grid eh-grid--3 eh-grid--gift-cards">
            <?php foreach (array_slice($giftCardAmounts, 0, 3) as $index => $gc): ?>
            <a href="/gift-card" class="eh-gift-card" data-reveal data-delay="<?= $index * 150 ?>">
                <div class="eh-gift-card__inner">
                    <div class="eh-gift-card__brand">Egoire</div>
                    <div class="eh-gift-card__amount"><?= formatPrice((float) $gc['amount']) ?></div>
                    <div class="eh-gift-card__label">Poklon kartica</div>
                    <div class="eh-gift-card__shimmer"></div>
                </div>
            </a>
            <?php endforeach; ?>
        </div>

        <div class="eh-section-footer">
            <a href="/gift-card" class="eh-btn eh-btn--outline">Sve poklon kartice</a>
        </div>
    </div>
</section>
<?php endif; ?>

<!-- ============================================================
     7. TESTIMONIALS  –  Centered slider
     ============================================================ -->

<section class="eh-section eh-section--testimonials" data-reveal>
    <div class="eh-container">
        <header class="eh-section-header">
            <span class="eh-section-header__eyebrow">Iskustva</span>
            <h2 class="eh-section-header__title">Reči naših kupaca</h2>
        </header>

        <div class="eh-testimonials" data-testimonials>
            <div class="eh-testimonials__track">
                <?php foreach ($testimonials as $t): ?>
                <div class="eh-testimonials__slide">
                    <blockquote class="eh-quote">
                        <svg class="eh-quote__mark" width="40" height="40" viewBox="0 0 40 40" fill="currentColor" opacity="0.15">
                            <path d="M10.3 28.7c-1.5-1.4-2.3-3.3-2.3-5.7 0-2.1.5-4 1.5-5.8 1-1.7 2.7-3.5 5.2-5.2l1.8 2.5c-1.7 1.2-2.9 2.3-3.5 3.3-.6 1-.9 2-.9 3h.2c.4-.5 1-.8 1.9-.8 1 0 1.9.4 2.6 1.1.7.7 1.1 1.6 1.1 2.8 0 1.2-.4 2.2-1.2 3-.8.8-1.8 1.2-3 1.2-1.5 0-2.6-.5-3.4-1.4zm14 0c-1.5-1.4-2.3-3.3-2.3-5.7 0-2.1.5-4 1.5-5.8 1-1.7 2.7-3.5 5.2-5.2l1.8 2.5c-1.7 1.2-2.9 2.3-3.5 3.3-.6 1-.9 2-.9 3h.2c.4-.5 1-.8 1.9-.8 1 0 1.9.4 2.6 1.1.7.7 1.1 1.6 1.1 2.8 0 1.2-.4 2.2-1.2 3-.8.8-1.8 1.2-3 1.2-1.5 0-2.6-.5-3.4-1.4z"/>
                        </svg>
                        <p class="eh-quote__text"><?= htmlspecialchars($t['text']) ?></p>
                        <footer class="eh-quote__footer">
                            <cite class="eh-quote__author"><?= htmlspecialchars($t['author']) ?></cite>
                            <span class="eh-quote__role"><?= htmlspecialchars($t['role']) ?></span>
                        </footer>
                    </blockquote>
                </div>
                <?php endforeach; ?>
            </div>
            <div class="eh-testimonials__dots"></div>
        </div>
    </div>
</section>

<!-- ============================================================
     8. BLOG  –  3-column grid
     ============================================================ -->

<?php if ($blogPosts): ?>
<section class="eh-section" data-reveal>
    <div class="eh-container">
        <header class="eh-section-header">
            <span class="eh-section-header__eyebrow">Inspiracija</span>
            <h2 class="eh-section-header__title">Iz našeg bloga</h2>
            <p class="eh-section-header__desc">Saveti, trendovi i stručni uvidi iz sveta premium nege kose</p>
        </header>

        <div class="eh-grid eh-grid--3">
            <?php foreach ($blogPosts as $index => $bp): ?>
            <a href="/blog/<?= htmlspecialchars($bp['slug']) ?>" class="eh-blog-card" data-reveal data-delay="<?= $index * 150 ?>">
                <div class="eh-blog-card__img">
                    <?php if ($bp['featured_image']): ?>
                    <img src="<?= htmlspecialchars($bp['featured_image']) ?>" alt="" loading="lazy">
                    <?php else: ?>
                    <div class="eh-blog-card__placeholder">Egoire Blog</div>
                    <?php endif; ?>
                </div>
                <div class="eh-blog-card__body">
                    <time class="eh-blog-card__date"><?= formatDate($bp['created_at']) ?></time>
                    <h3 class="eh-blog-card__title"><?= htmlspecialchars($bp['title']) ?></h3>
                    <p class="eh-blog-card__excerpt"><?= htmlspecialchars($bp['excerpt'] ?? truncate(strip_tags($bp['body']), 120)) ?></p>
                    <span class="eh-blog-card__link">Pročitaj više →</span>
                </div>
            </a>
            <?php endforeach; ?>
        </div>

        <div class="eh-section-footer">
            <a href="/blog" class="eh-btn eh-btn--outline">Svi postovi</a>
        </div>
    </div>
</section>
<?php endif; ?>

<!-- ============================================================
     9. CONTACT  –  Two-column layout
     ============================================================ -->

<section class="eh-section eh-section--contact" data-reveal>
    <div class="eh-container">
        <div class="eh-contact">
            <div class="eh-contact__info" data-reveal data-delay="0">
                <span class="eh-section-header__eyebrow">Pišite nam</span>
                <h2 class="eh-section-header__title">Kontaktirajte nas</h2>
                <p class="eh-contact__desc">
                    Imate pitanje ili želite personalizovanu preporuku? Naš tim stručnjaka
                    je tu za vas — javite nam se i odgovorićemo u najkraćem roku.
                </p>
                <div class="eh-contact__details">
                    <div class="eh-contact__item">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/></svg>
                        <div>
                            <span class="eh-contact__label">Email</span>
                            <a href="mailto:info@egoire.rs">info@egoire.rs</a>
                        </div>
                    </div>
                    <div class="eh-contact__item">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M22 16.92v3a2 2 0 01-2.18 2 19.79 19.79 0 01-8.63-3.07 19.5 19.5 0 01-6-6 19.79 19.79 0 01-3.07-8.67A2 2 0 014.11 2h3a2 2 0 012 1.72 12.84 12.84 0 00.7 2.81 2 2 0 01-.45 2.11L8.09 9.91a16 16 0 006 6l1.27-1.27a2 2 0 012.11-.45 12.84 12.84 0 002.81.7A2 2 0 0122 16.92z"/></svg>
                        <div>
                            <span class="eh-contact__label">Telefon</span>
                            <a href="tel:+381641234567">+381 64 123 4567</a>
                        </div>
                    </div>
                    <div class="eh-contact__item">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                        <div>
                            <span class="eh-contact__label">Radno vreme</span>
                            <span>Pon – Pet: 09:00 – 17:00</span>
                        </div>
                    </div>
                </div>
            </div>
            <div class="eh-contact__form-wrap" data-reveal data-delay="200">
                <form class="eh-contact__form" id="ehContactForm" method="POST" action="/api/contact">
                    <input type="hidden" name="csrf_token" value="<?= csrfToken() ?>">
                    <div class="eh-form-row eh-form-row--2">
                        <div class="eh-form-group">
                            <label class="eh-label" for="ehName">Ime *</label>
                            <input class="eh-input" type="text" id="ehName" name="name" required autocomplete="name">
                        </div>
                        <div class="eh-form-group">
                            <label class="eh-label" for="ehEmail">Email *</label>
                            <input class="eh-input" type="email" id="ehEmail" name="email" required autocomplete="email">
                        </div>
                    </div>
                    <div class="eh-form-group">
                        <label class="eh-label" for="ehSubject">Naslov*</label>
                        <input class="eh-input" type="text" id="ehSubject" name="subject">
                    </div>
                    <div class="eh-form-group">
                        <label class="eh-label" for="ehMessage">Poruka *</label>
                        <textarea class="eh-input eh-input--textarea" id="ehMessage" name="message" rows="5" required></textarea>
                    </div>
                    <button class="eh-btn eh-btn--primary eh-btn--full" type="submit">Pošalji poruku</button>
                    <div class="eh-form-feedback" id="ehContactFeedback" hidden></div>
                </form>
            </div>
        </div>
    </div>
</section>

<?php require __DIR__ . '/../layout/footer.php'; ?>
