/* ==========================================================================
   Egoire – Admin Panel JavaScript
   ========================================================================== */
document.addEventListener('DOMContentLoaded', function () {

    /* ---------- Sidebar Toggle (Mobile) ---------- */
    const sidebarToggle = document.querySelector('.admin-sidebar-toggle');
    const sidebar = document.querySelector('.admin-sidebar');
    const sidebarOverlay = document.querySelector('.admin-sidebar-overlay');

    if (sidebarToggle && sidebar) {
        sidebarToggle.addEventListener('click', function () {
            sidebar.classList.toggle('open');
            if (sidebarOverlay) sidebarOverlay.classList.toggle('active');
            document.body.style.overflow = sidebar.classList.contains('open') ? 'hidden' : '';
        });

        if (sidebarOverlay) {
            sidebarOverlay.addEventListener('click', function () {
                sidebar.classList.remove('open');
                sidebarOverlay.classList.remove('active');
                document.body.style.overflow = '';
            });
        }
    }

    /* ---------- Delete Confirmation ---------- */
    document.querySelectorAll('[data-confirm]').forEach(function (el) {
        el.addEventListener('click', function (e) {
            if (!confirm(this.dataset.confirm || 'Da li ste sigurni?')) {
                e.preventDefault();
            }
        });
    });

    /* ---------- Dynamic Rows (Stock Variants / Tier Discounts) ---------- */
    // Add Row
    document.querySelectorAll('.add-row-btn').forEach(function (btn) {
        btn.addEventListener('click', function () {
            const container = document.querySelector(this.dataset.target);
            if (!container) return;
            const template = container.querySelector('.dynamic-row');
            if (!template) return;

            const clone = template.cloneNode(true);
            // Clear inputs
            clone.querySelectorAll('input').forEach(function (inp) { inp.value = ''; });
            clone.querySelectorAll('select').forEach(function (sel) { sel.selectedIndex = 0; });
            container.appendChild(clone);

            // Bind remove
            const removeBtn = clone.querySelector('.remove-row');
            if (removeBtn) {
                removeBtn.addEventListener('click', function () {
                    if (container.querySelectorAll('.dynamic-row').length > 1) {
                        clone.remove();
                    }
                });
            }
        });
    });

    // Remove Row (existing)
    document.querySelectorAll('.remove-row').forEach(function (btn) {
        btn.addEventListener('click', function () {
            const container = this.closest('.dynamic-rows');
            if (container && container.querySelectorAll('.dynamic-row').length > 1) {
                this.closest('.dynamic-row').remove();
            }
        });
    });

    /* ---------- Image Preview on Upload ---------- */
    document.querySelectorAll('input[type="file"][data-preview]').forEach(function (input) {
        input.addEventListener('change', function () {
            const previewId = this.dataset.preview;
            const preview = document.getElementById(previewId);
            if (!preview || !this.files || !this.files[0]) return;

            const reader = new FileReader();
            reader.onload = function (e) {
                preview.src = e.target.result;
                preview.style.display = 'block';
            };
            reader.readAsDataURL(this.files[0]);
        });
    });

    /* ---------- Tabs ---------- */
    document.querySelectorAll('.tab').forEach(function (tab) {
        tab.addEventListener('click', function () {
            const group = this.closest('.card') || document;
            // Deactivate all tabs in group
            group.querySelectorAll('.tab').forEach(function (t) { t.classList.remove('active'); });
            group.querySelectorAll('.tab-content').forEach(function (tc) { tc.classList.remove('active'); });
            // Activate clicked
            this.classList.add('active');
            const target = document.getElementById(this.dataset.tab);
            if (target) target.classList.add('active');
        });
    });

    /* ---------- Select All Checkbox ---------- */
    const selectAll = document.getElementById('select-all');
    if (selectAll) {
        selectAll.addEventListener('change', function () {
            const checked = this.checked;
            document.querySelectorAll('.row-checkbox').forEach(function (cb) {
                cb.checked = checked;
            });
        });
    }

    /* ---------- Contact Status Inline Update ---------- */
    document.querySelectorAll('.contact-status-select').forEach(function (select) {
        select.addEventListener('change', function () {
            const id = this.dataset.id;
            const status = this.value;
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = '/admin/contacts';
            form.innerHTML =
                '<input type="hidden" name="action" value="update_status">' +
                '<input type="hidden" name="id" value="' + id + '">' +
                '<input type="hidden" name="status" value="' + status + '">' +
                '<input type="hidden" name="csrf_token" value="' + (document.querySelector('meta[name="csrf-token"]')?.content || '') + '">';
            document.body.appendChild(form);
            form.submit();
        });
    });

    /* ---------- Auto-slug Generation ---------- */
    const nameInput = document.getElementById('name') || document.getElementById('title');
    const slugInput = document.getElementById('slug');
    let autoSlug = true;

    if (nameInput && slugInput) {
        // Only auto-generate if slug is empty (new item)
        if (slugInput.value) autoSlug = false;

        slugInput.addEventListener('input', function () {
            autoSlug = false;
        });

        nameInput.addEventListener('input', function () {
            if (!autoSlug) return;
            let slug = this.value.toLowerCase()
                .replace(/č/g, 'c').replace(/ć/g, 'c')
                .replace(/đ/g, 'dj').replace(/š/g, 's').replace(/ž/g, 'z')
                .replace(/[^a-z0-9\s-]/g, '')
                .replace(/\s+/g, '-')
                .replace(/-+/g, '-')
                .replace(/^-|-$/g, '');
            slugInput.value = slug;
        });
    }

    /* ---------- Order Status Change Confirm ---------- */
    const statusForm = document.getElementById('status-form');
    if (statusForm) {
        statusForm.addEventListener('submit', function (e) {
            if (!confirm('Promeniti status porudžbine?')) {
                e.preventDefault();
            }
        });
    }

    /* ---------- Campaign Send Confirm ---------- */
    document.querySelectorAll('.send-campaign-btn').forEach(function (btn) {
        btn.addEventListener('click', function (e) {
            if (!confirm('Da li ste sigurni da želite da pošaljete ovu kampanju?')) {
                e.preventDefault();
            }
        });
    });

    /* ---------- Auto-hide flash messages ---------- */
    document.querySelectorAll('.flash').forEach(function (flash) {
        setTimeout(function () {
            flash.style.transition = 'opacity .5s';
            flash.style.opacity = '0';
            setTimeout(function () { flash.remove(); }, 500);
        }, 5000);
    });

    /* ---------- Sidebar Active Link ---------- */
    const currentPath = window.location.pathname;
    document.querySelectorAll('.admin-nav a').forEach(function (link) {
        const href = link.getAttribute('href');
        if (href === currentPath || (href !== '/admin' && currentPath.startsWith(href))) {
            link.classList.add('active');
        }
    });

    /* ---------- Print Button ---------- */
    document.querySelectorAll('.print-btn').forEach(function (btn) {
        btn.addEventListener('click', function () {
            window.print();
        });
    });

    /* ---------- Tooltip Init (simple) ---------- */
    document.querySelectorAll('[data-tooltip]').forEach(function (el) {
        el.style.position = 'relative';
        el.addEventListener('mouseenter', function () {
            const tip = document.createElement('div');
            tip.className = 'tooltip-popup';
            tip.textContent = this.dataset.tooltip;
            tip.style.cssText = 'position:absolute;bottom:100%;left:50%;transform:translateX(-50%);background:#1a1a1a;color:#fff;padding:4px 10px;border-radius:4px;font-size:12px;white-space:nowrap;z-index:999;margin-bottom:6px;';
            this.appendChild(tip);
        });
        el.addEventListener('mouseleave', function () {
            const tip = this.querySelector('.tooltip-popup');
            if (tip) tip.remove();
        });
    });
});
