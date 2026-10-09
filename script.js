// ---------------- Switch between login and register with smooth transition ----------------
function showform(formId) {
  document.querySelectorAll(".form-box").forEach(form => form.classList.remove("active"));
  document.getElementById(formId).classList.add("active");
}

// ---------------- Icon buttons focus input (exclude password toggles) ----------------
document.querySelectorAll(".icon-btn:not(.password-toggle)").forEach(btn => {
  btn.addEventListener("click", () => {
    const input = btn.parentElement.querySelector("input");
    if (input) input.focus();
  });
});

// ---------------- Password toggle ----------------
document.querySelectorAll(".password-toggle").forEach(btn => {
  const input = btn.parentElement.querySelector("input");
  const icon  = btn.querySelector("i");

  const blockFocusSteal = e => e.preventDefault();
  btn.addEventListener("mousedown", blockFocusSteal);
  btn.addEventListener("touchstart", blockFocusSteal, { passive: false });

  let state = "lock";

  function setState(next) {
    state = next;
    if (state === "lock") {
      icon.className = "fa fa-lock";
      input.type = "password";
    } else if (state === "hidden") {
      icon.className = "fa fa-eye-slash";
      input.type = "password";
    } else if (state === "shown") {
      icon.className = "fa fa-eye";
      input.type = "text";
    }
  }

  setState("lock");

  btn.addEventListener("click", e => {
    e.preventDefault();
    e.stopPropagation();

    if (state === "lock") {
      setState("hidden");
      input.focus();
    } else if (state === "hidden") {
      setState("shown");
      input.focus();
    } else {
      setState("hidden");
      input.focus();
    }
  });

  input.addEventListener("blur", () => setState("lock"));
});

// ---------------- Enforce password min length 5 ----------------
document.querySelectorAll('input[name="password"]').forEach(pw => {
  pw.addEventListener("input", () => {
    pw.setCustomValidity(pw.value.length >= 5 ? "" : "Password must be at least 5 characters");
  });
});

// ---------------- Phone number mask ----------------
const phoneInput = document.getElementById("phone");
const mask = "0000-000-0000";

if (phoneInput) {
  phoneInput.addEventListener("focus", function () {
    if (!this.value) this.value = mask;
    setTimeout(() => {
      const firstZero = this.value.indexOf("0");
      this.setSelectionRange(firstZero, firstZero);
    }, 0);
  });

  phoneInput.addEventListener("keydown", function (e) {
    let pos = this.selectionStart;
    let valueArr = this.value.split("");

    if (["ArrowLeft", "ArrowRight", "Tab"].includes(e.key)) return;

    if (e.key === "Backspace") {
      e.preventDefault();
      while (pos > 0 && valueArr[pos - 1] === "-") pos--;
      if (pos > 0 && valueArr[pos - 1] !== "0") {
        valueArr[pos - 1] = "0";
        pos--;
      }
      this.value = valueArr.join("");
      this.setSelectionRange(pos, pos);
      return;
    }

    if (/[0-9]/.test(e.key)) {
      e.preventDefault();
      while (pos < valueArr.length && valueArr[pos] === "-") pos++;
      if (pos < valueArr.length) {
        valueArr[pos] = e.key;
        pos++;
        if (valueArr[pos] === "-") pos++;
        this.value = valueArr.join("");
        this.setSelectionRange(pos, pos);
      }
    }
  });

  phoneInput.addEventListener("blur", function () {
    if (this.value === mask) this.value = "";
  });
}

// ---------------- Calendar button ----------------
const calendarIcon = document.getElementById("calendar-icon");
if (calendarIcon) {
  calendarIcon.addEventListener("click", function () {
    const dateInput = document.getElementById("birthdate");
    if (dateInput.showPicker) {
      dateInput.showPicker();
    } else {
      dateInput.focus();
    }
  });
}

// ---------------- Auto age calculation ----------------
const birthdateInput = document.getElementById("birthdate");
if (birthdateInput) {
  birthdateInput.addEventListener("change", function () {
    const birthdate = new Date(this.value);
    const today = new Date();

    let age = today.getFullYear() - birthdate.getFullYear();
    const monthDiff = today.getMonth() - birthdate.getMonth();
    if (monthDiff < 0 || (monthDiff === 0 && today.getDate() < birthdate.getDate())) {
      age--;
    }

    document.querySelector('input[name="age"]').value = age >= 0 ? age : "";
  });
}

/*PROFILE */
 document.addEventListener("DOMContentLoaded", () => {
      const icon = document.getElementById("userIcon");
      const dropdown = document.getElementById("dropdown");

      icon.addEventListener("click", e => {
        e.stopPropagation();
        dropdown.classList.toggle("show");
      });

      window.addEventListener("click", e => {
        if (!e.target.closest(".profile-menu")) {
          dropdown.classList.remove("show");
        }
      });
    });




document.addEventListener("DOMContentLoaded", () => {
  const img = document.getElementById("profileImg");
  const actionButtons = document.getElementById("actionButtons");
  const cancelBtn = document.getElementById("cancelBtn");

  // Accordion toggle
  const accBtn = document.querySelector(".accordion-btn");
  const accContent = document.querySelector(".accordion-content");
  accBtn.addEventListener("click", () => {
    accContent.style.maxHeight = accContent.style.maxHeight
      ? null
      : accContent.scrollHeight + "px";
  });

  // Image upload preview
  const fileInput = document.getElementById("newImage");
  const originalSrc = img.src;
  let previewURL = null;

  fileInput.addEventListener("change", () => {
    const file = fileInput.files[0];
    if (!file) return;
    if (previewURL) URL.revokeObjectURL(previewURL);
    previewURL = URL.createObjectURL(file);
    img.src = previewURL;
    actionButtons.classList.remove("hidden");
  });

  cancelBtn.addEventListener("click", () => {
    if (previewURL) URL.revokeObjectURL(previewURL);
    img.src = originalSrc;
    actionButtons.classList.add("hidden");
    fileInput.value = "";
  });
});


/* shop page */
// Make homepage products clickable -> go to shop.html
document.querySelectorAll(".home-products .pro").forEach(card => {
  card.addEventListener("click", () => {
    window.location.href = "shop.php";
  });
});


// ---------------- Toggle top-level filters (with smooth transition + close others) ----------------
document.querySelectorAll('.filter-toggle').forEach(button => {
  button.addEventListener('click', () => {
    const options = button.nextElementSibling;

    // Close all other filter groups first
    document.querySelectorAll('.filter-options').forEach(opt => {
      if (opt !== options) opt.classList.remove('open');
    });

    // Toggle the clicked one
    options.classList.toggle('open');
  });
});

// ---------------- Toggle nested filters (with smooth transition + close others) ----------------
document.querySelectorAll('.nested-toggle').forEach(button => {
  button.addEventListener('click', () => {
    const nested = button.nextElementSibling;

    // Close other nested groups
    document.querySelectorAll('.nested-options').forEach(opt => {
      if (opt !== nested) opt.classList.remove('open');
    });

    // Toggle current one
    nested.classList.toggle('open');
  });
});

// ---------------- Filter products (only one checkbox active) ----------------
document.querySelectorAll(".filter").forEach(checkbox => {
  checkbox.addEventListener("change", (e) => {
    // ✅ Ensure only one filter can be active at a time
    if (e.target.checked) {
      document.querySelectorAll(".filter").forEach(cb => {
        if (cb !== e.target) cb.checked = false;
      });
    }

    const checkedFilters = Array.from(document.querySelectorAll(".filter:checked"))
      .map(cb => cb.dataset.filter);

    document.querySelectorAll(".pro").forEach(product => {
      const categories = (product.dataset.category || '').trim() === ''
        ? []
        : product.dataset.category.split(/\s+/);

      const match = checkedFilters.every(f => categories.includes(f));

      // ✅ Keep flex display to preserve grid layout
      product.style.display = (checkedFilters.length === 0 || match) ? "flex" : "none";
    });
  });
});

// ...existing code...

  // ===== append / integrate: shop stock handling =====
  document.addEventListener('DOMContentLoaded', () => {

    // Apply stock classes to cards (expects data-stock attribute on each .pro)
    document.querySelectorAll('#product1 .pro, .pro').forEach(card => {
      const stockAttr = card.getAttribute('data-stock') ?? card.dataset.stock;
      if (stockAttr == null) return;
      const stock = parseInt(stockAttr, 10);
      if (isNaN(stock)) return;

      // ensure data-stock string is present for badge content
      card.setAttribute('data-stock', String(stock));

      if (stock <= 0) {
        card.classList.add('out-of-stock');
        card.classList.remove('low-stock');
        // mark interactive elements disabled for accessibility (only buttons/links)
        card.querySelectorAll('a, button, .cart, .add-to-cart').forEach(el => {
          el.classList.add('disabled');
          el.setAttribute('aria-disabled','true');
        });
      } else if (stock < 10) {
        card.classList.add('low-stock');
        card.classList.remove('out-of-stock');
      } else {
        card.classList.remove('low-stock','out-of-stock');
      }
    });

    // --- Allow clicking the product card to open the product page ---
    // Only prevent navigation when the click originated on the "add-to-cart" control
    document.querySelectorAll('#product1 .pro').forEach(card => {
      card.addEventListener('click', (e) => {
        // if user clicked the add-to-cart button (or an element inside it), let that button's handler run
        if (e.target.closest('.add-to-cart') || e.target.closest('.cart')) return;

        // Navigate to product page if data-link is present
        const link = card.dataset.link;
        if (link) window.location.href = link;
      });
    });

    // --- Homepage quick-clicks: go to shop or product (don't block navigation for out-of-stock) ---
    document.querySelectorAll('.home-products .pro').forEach(card => {
      card.addEventListener('click', (e) => {
        if (e.target.closest('.add-to-cart') || e.target.closest('.cart')) return;
        const link = card.dataset.link || "shop.php";
        window.location.href = link;
      });
    });

  });
  
// ...existing code...






// CART JS WITH STOCK LIMIT
document.addEventListener("DOMContentLoaded", () => {
  const totalDisplay = document.querySelector(".total-price");
  const cartForm = document.getElementById("checkout-form");

  // ----------------------------------------------------
  // ADD TO CART BUTTON
  // ----------------------------------------------------
  document.querySelectorAll(".add-to-cart").forEach(button => {
    button.addEventListener("click", e => {
      e.stopPropagation();
      const productId = button.getAttribute("data-id");
      const qtyInput = document.querySelector("#product-qty");
      const quantity = qtyInput ? qtyInput.value : 1;

      fetch("add_to_cart.php", {
        method: "POST",
        headers: { "Content-Type": "application/x-www-form-urlencoded" },
        body: `product_id=${productId}&quantity=${quantity}`
      })
      .then(res => res.json())
      .then(data => {
        if (data.success) {
          showNotification("✅ Added to cart!");
        } else {
          showNotification(`⚠ ${data.error}`, true);
        }
      })
      .catch(err => console.error("Fetch error:", err));
    });
  });

  // ----------------------------------------------------
  // HIGHLIGHT TO SELECT ITEMS
  // ----------------------------------------------------
  document.addEventListener("click", e => {
    if (!e.target.classList.contains("add-icon")) return;
    const row = e.target.closest("tr");
    if (!row) return;

    row.classList.toggle("highlight");

    let hidden = row.querySelector("input[name='selected[]']");
    if (!hidden) {
      hidden = document.createElement("input");
      hidden.type = "hidden";
      hidden.name = "selected[]";
      row.appendChild(hidden);
    }
    hidden.value = row.classList.contains("highlight")
      ? row.dataset.cartId
      : "";

    updateTotal();
  });

  // ----------------------------------------------------
  // QUANTITY CHANGE HANDLER WITH STOCK CHECK
  // ----------------------------------------------------
  document.querySelectorAll("#cart input[type='number']").forEach(input => {
    input.addEventListener("change", () => {
      const row = input.closest("tr");
      const cartId = row.dataset.cartId;
      const stock = parseInt(row.dataset.stock, 10) || Infinity;
      let newQty = Math.max(1, Number(input.value) || 1);

      if (newQty > stock) {
        newQty = stock;
        input.value = stock;
        showNotification(`⚠ Only ${stock} available.`, true);
      }

      if (newQty < 1) {
        newQty = 1;
        input.value = 1;
      }

      // POST updated quantity to server
      fetch("update_cart.php", {
        method: "POST",
        headers: { "Content-Type": "application/x-www-form-urlencoded" },
        body: `cart_id=${encodeURIComponent(cartId)}&quantity=${encodeURIComponent(newQty)}`
      })
      .then(res => res.json())
      .then(data => {
        if (!data || !data.success) {
          showNotification(`⚠ ${data?.error ?? 'Failed to update cart'}`, true);
          return;
        }

        // Parse numeric values returned as formatted strings (remove commas)
        const unitPriceNum = parseFloat((data.price ?? "").replace(/,/g, "")) || 0;
        const subtotalNum = parseFloat((data.subtotal ?? "").replace(/,/g, "")) || (unitPriceNum * (data.quantity || newQty));

        // update DOM: data-unit-price (used by updateTotal) and visible subtotal
        const priceCell = row.querySelector(".price");
        if (priceCell) {
          priceCell.setAttribute("data-unit-price", String(unitPriceNum));
          priceCell.textContent = `₱${subtotalNum.toLocaleString(undefined, { minimumFractionDigits: 2 })}`;
        }

        // ensure input reflects server-canonical quantity
        input.value = data.quantity ?? newQty;

        updateTotal();
      })
      .catch(err => {
        console.error("Update cart error:", err);
        showNotification("Network error while updating cart.", true);
      });
    });
  });

  // ----------------------------------------------------
  // CHECKOUT: ONLY HIGHLIGHTED ITEMS
  // ----------------------------------------------------
  if (cartForm) {
    cartForm.addEventListener("submit", e => {
      const highlightedRows = Array.from(document.querySelectorAll("#cart tr.highlight"));
      if (highlightedRows.length === 0) {
        e.preventDefault();
        showNotification("Please highlight at least one product to proceed.", true);
        return;
      }

      const payload = highlightedRows.map(row => ({
        cart_id: row.dataset.cartId,
        id: row.dataset.productId,
        name: row.dataset.productName,
        quantity: parseInt(row.querySelector("input[type='number']").value, 10),
        price: parseFloat(row.querySelector(".price").getAttribute("data-unit-price"))
      }));

      const hiddenField = document.getElementById("highlighted");
      if (hiddenField) hiddenField.value = JSON.stringify(payload);
    });
  }

  // ----------------------------------------------------
  // TOTAL CALCULATION (HIGHLIGHTED ONLY)
  // ----------------------------------------------------
  function updateTotal() {
    let total = 0;
    const highlighted = Array.from(document.querySelectorAll("#cart tr.highlight"));
    highlighted.forEach(row => {
      const qtyInput = row.querySelector("input[type='number']");
      const qty = qtyInput ? parseInt(qtyInput.value) || 0 : 0;
      const unitPrice = parseFloat(row.querySelector(".price").getAttribute("data-unit-price")) || 0;
      total += qty * unitPrice;
    });

    if (totalDisplay) totalDisplay.textContent = "₱" + total.toLocaleString(undefined, { minimumFractionDigits: 2 });
  }

  updateTotal();

  // ----------------------------------------------------
  // NOTIFICATION HELPER
  // ----------------------------------------------------
  function showNotification(message, isError = false) {
    const note = document.createElement("div");
    note.textContent = message;
    note.style.position = "fixed";
    note.style.top = "10px";
    note.style.left = "50%";
    note.style.transform = "translateX(-50%)";
    note.style.padding = "10px 14px";
    note.style.borderRadius = "8px";
    note.style.color = "#fff";
    note.style.fontWeight = "600";
    note.style.background = isError ? "#e74c3c" : "#5779a1";
    note.style.zIndex = 2000;
    document.body.appendChild(note);

    void note.offsetHeight;
    note.style.transition = "transform 0.5s ease, opacity 0.5s ease";
    note.style.transform = "translateX(-50%) translateY(60px)";
    note.style.opacity = "1";

    setTimeout(() => {
      note.style.transform = "translateX(-50%) translateY(-120%)";
      note.style.opacity = "0";
      setTimeout(() => note.remove(), 500);
    }, 2000);
  }
});







//DIY
function loadComponents(type, containerId, search = '') {
    fetch(`get_components.php?type=${type}&search=${encodeURIComponent(search)}`)
        .then(res => res.json())
        .then(data => {
            const container = document.getElementById(containerId);
            container.innerHTML = '';

            if (data.error) {
                container.innerHTML = `<p>${data.error}</p>`;
                return;
            }

            data.forEach(item => {
                const div = document.createElement('div');
                div.className = 'product-card';
                div.innerHTML = `
                    <img src="${item.image_url}" alt="${item.name}" />
                    <h4>${item.name}</h4>
                    <p>₱${item.price.toLocaleString()}</p>
                    <button data-id="${item.id}" class="add-btn">Add</button>
                `;
                container.appendChild(div);
            });
        });
}

// Example: load Intel CPUs
loadComponents('cpu_intel', 'cpu-container');

// Example: load all motherboards
loadComponents('motherboard', 'mobo-container');

// Example: search SSD
loadComponents('ssd', 'ssd-container', 'Crucial');
