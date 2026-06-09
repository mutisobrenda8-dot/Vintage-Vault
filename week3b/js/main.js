// /week1-brenda/week1b//js/main.js

// 1. SMOOTH PAGE LOAD
document.body.style.opacity    = '0';
document.body.style.transition = 'opacity 0.3s ease';
window.addEventListener('load', () => {
    document.body.style.opacity = '1';
});

// 2. AUTO HIDE ALERTS
document.querySelectorAll('.vv-alert-success, .vv-alert-error')
    .forEach(el => {
        setTimeout(() => {
            el.style.transition = 'opacity 0.5s';
            el.style.opacity    = '0';
            setTimeout(() => el.remove(), 500);
        }, 4000);
    });

// 3. TOAST NOTIFICATION
function showToast(message) {
    let toast = document.getElementById('cartToast');
    if (!toast) {
        toast = document.createElement('div');
        toast.id = 'cartToast';
        toast.style.cssText = `
            position:fixed; bottom:28px; right:28px;
            background:#3d2b1f; color:#c9b49a;
            padding:14px 22px;
            border-left:3px solid #7a5230;
            font-family:sans-serif; font-size:0.85rem;
            z-index:9999;
            box-shadow:0 4px 20px rgba(0,0,0,0.25);
            border-radius:2px;
            transition:opacity 0.4s ease;
        `;
        document.body.appendChild(toast);
    }
    toast.textContent   = message;
    toast.style.opacity = '1';
    toast.style.display = 'block';
    clearTimeout(window.toastTimer);
    window.toastTimer = setTimeout(() => {
        toast.style.opacity = '0';
        setTimeout(() => { toast.style.display = 'none'; }, 400);
    }, 3000);
}

// 4. UPDATE CART BADGE
function updateCartCount(count) {
    let badge = document.querySelector('.vv-badge');
    if (count > 0) {
        if (badge) {
            badge.textContent = count;
        } else {
            const cartLink = document.querySelector('a[href*="cart.php"]');
            if (cartLink) {
                const b       = document.createElement('span');
                b.className   = 'badge vv-badge';
                b.textContent = count;
                cartLink.appendChild(b);
            }
        }
    }
}

// 5. ADD TO CART
document.querySelectorAll('.add-to-cart-btn').forEach(btn => {
    btn.addEventListener('click', function () {
        const productId   = this.dataset.id;
        const productName = this.dataset.name;
        const button      = this;

        button.textContent = 'Adding...';
        button.disabled    = true;

        fetch('//week1-brenda/week1b//cart_action.php', {
            method:  'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body:    `action=add&product_id=${productId}`
        })
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                showToast(`✓ "${productName}" added to cart!`);
                updateCartCount(data.cart_count);
                button.textContent      = '✓ Added!';
                button.style.background = '#2d7a4f';
                setTimeout(() => {
                    button.textContent      = 'Add to Cart';
                    button.style.background = '';
                    button.disabled         = false;
                }, 2000);
            } else if (data.redirect) {
                showToast('Please log in to add items to cart!');
                setTimeout(() => {
                    window.location.href = '//week1-brenda/week1b//login.php';
                }, 1500);
                button.textContent = 'Add to Cart';
                button.disabled    = false;
            } else {
                showToast(data.message || 'Could not add to cart.');
                button.textContent = 'Add to Cart';
                button.disabled    = false;
            }
        })
        .catch(() => {
            showToast('Something went wrong. Try again.');
            button.textContent = 'Add to Cart';
            button.disabled    = false;
        });
    });
});

// 6. LIVE SEARCH
const searchInput    = document.getElementById('heroSearch');
const suggestionsBox = document.getElementById('searchSuggestions');

if (searchInput && suggestionsBox) {
    let timer;
    searchInput.addEventListener('input', function () {
        const q = this.value.trim();
        clearTimeout(timer);
        if (q.length < 2) {
            suggestionsBox.style.display = 'none';
            return;
        }
        timer = setTimeout(() => {
            fetch(`//week1-brenda/week1b//search_suggestions.php?q=${encodeURIComponent(q)}`)
            .then(r => r.json())
            .then(results => {
                if (!results.length) {
                    suggestionsBox.style.display = 'none';
                    return;
                }
                suggestionsBox.innerHTML = results.map(item => `
                    <div onclick="window.location='//week1-brenda/week1b//product.php?id=${item.id}'"
                         style="padding:10px 16px; cursor:pointer;
                                border-bottom:1px solid #ede4d3;
                                font-size:0.88rem; color:#4a3628;
                                display:flex; justify-content:space-between;
                                background:#faf6ef;">
                        <span>${item.name}</span>
                        <span style="color:#7a5230;font-weight:700;">
                            $${item.price}
                        </span>
                    </div>`).join('');
                suggestionsBox.style.cssText = `
                    display:block; position:absolute;
                    background:#faf6ef; border:1px solid #c9b49a;
                    width:100%; max-width:480px;
                    left:50%; transform:translateX(-50%);
                    z-index:1000;
                    box-shadow:0 4px 12px rgba(0,0,0,0.15);`;
            })
            .catch(() => { suggestionsBox.style.display = 'none'; });
        }, 300);
    });

    document.addEventListener('click', e => {
        if (!searchInput.contains(e.target) &&
            !suggestionsBox.contains(e.target)) {
            suggestionsBox.style.display = 'none';
        }
    });

    searchInput.addEventListener('keypress', function (e) {
        if (e.key === 'Enter') {
            window.location.href =
                `//week1-brenda/week1b//shop.php?search=${encodeURIComponent(this.value)}`;
        }
    });
}

// 7. CART QUANTITY BUTTONS
document.querySelectorAll('.qty-btn').forEach(btn => {
    btn.addEventListener('click', function () {
        const productId  = this.dataset.id;
        const action     = this.dataset.action;
        const qtyDisplay = document.getElementById(`qty-${productId}`);
        if (!qtyDisplay) return;

        let newQty = parseInt(qtyDisplay.textContent);
        newQty     = action === 'increase' ? newQty + 1 : newQty - 1;
        if (newQty < 1) newQty = 1;

        fetch('//week1-brenda/week1b//cart_action.php', {
            method:  'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body:    `action=update&product_id=${productId}&quantity=${newQty}`
        })
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                qtyDisplay.textContent = newQty;
                const sub = document.getElementById(`subtotal-${productId}`);
                if (sub && data.item_total) {
                    sub.textContent = `$${data.item_total}`;
                }
                const total = document.getElementById('cartTotal');
                if (total && data.cart_total) {
                    total.textContent = `$${data.cart_total}`;
                }
                updateCartCount(data.cart_count);
            }
        });
    });
});

// 8. REMOVE CART ITEM
document.querySelectorAll('.remove-item-btn').forEach(btn => {
    btn.addEventListener('click', function () {
        const productId = this.dataset.id;
        const row       = document.getElementById(`cart-row-${productId}`);

        fetch('//week1-brenda/week1b//cart_action.php', {
            method:  'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body:    `action=remove&product_id=${productId}`
        })
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                if (row) {
                    row.style.transition = 'opacity 0.3s';
                    row.style.opacity    = '0';
                    setTimeout(() => row.remove(), 300);
                }
                updateCartCount(data.cart_count);
                showToast('Item removed from cart.');
                if (data.cart_count === 0) {
                    setTimeout(() => location.reload(), 800);
                }
            }
        });
    });
});