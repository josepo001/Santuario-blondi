document.addEventListener("DOMContentLoaded", () => {
    const cart = [];
    const productList = document.getElementById("product-list");
    const cartList = document.getElementById("cart-list");
    const cartIcon = document.getElementById("cart-icon");
    const cartPopup = document.getElementById("cart-popup");
    const cartCount = document.getElementById("cart-count");
    const subtotalElement = document.getElementById("subtotal");
    const totalElement = document.getElementById("total");
    const checkoutButton = document.getElementById("checkout-button");
    const closeCartButton = document.getElementById("close-cart");

    // Agregar producto al carrito
    productList.addEventListener("click", (e) => {
        if (e.target.classList.contains("add-to-cart")) {
            const id = e.target.dataset.id;
            const nombre = e.target.dataset.nombre;
            const precio = parseFloat(e.target.dataset.precio);

            // Añadir producto al carrito
            cart.push({ id, nombre, precio });
            updateCart();
        }
    });

    // Mostrar carrito al hacer clic en el ícono del carrito
    cartIcon.addEventListener("click", () => {
        cartPopup.classList.toggle("show");
    });
    

    // Ocultar carrito al hacer clic en el botón de cerrar
    closeCartButton.addEventListener("click", () => {
        cartPopup.classList.add("hidden");
    });

    // Actualizar carrito
    function updateCart() {
        // Limpiar lista del carrito
        cartList.innerHTML = "";

        let subtotal = 0;

        // Recorrer el carrito y generar los elementos de la lista
        cart.forEach((item, index) => {
            subtotal += item.precio;

            const li = document.createElement("li");
            li.innerHTML = `
                ${item.nombre} - $${item.precio.toLocaleString()} 
                <button class="remove-item" data-index="${index}">X</button>
            `;
            cartList.appendChild(li);
        });

        // Calcular el total
        const total = subtotal; // Aquí puedes añadir impuestos o descuentos
        subtotalElement.textContent = `$${subtotal.toLocaleString()}`;
        totalElement.textContent = `$${total.toLocaleString()}`;

        // Actualizar el contador del carrito
        cartCount.textContent = cart.length;

        // Mostrar botón de pagar si hay productos en el carrito
        checkoutButton.style.display = cart.length > 0 ? "block" : "none";

        // Agregar eventos para eliminar productos
        const removeButtons = document.querySelectorAll(".remove-item");
        removeButtons.forEach((button) => {
            button.addEventListener("click", (e) => {
                const index = parseInt(e.target.dataset.index, 10);
                cart.splice(index, 1); // Eliminar producto del carrito
                updateCart(); // Actualizar carrito
            });
        });

        // Guardar el carrito en la sesión (opcional, si usas backend PHP)
        saveCartToSession(cart);
    }

    // Función para guardar el carrito en la sesión (opcional, requiere PHP backend)
    function saveCartToSession(cart) {
        fetch("./api/guardar_carrito.php", {
            method: "POST",
            headers: {
                "Content-Type": "application/json",
            },
            body: JSON.stringify({ cart }),
        })
            .then((response) => response.json())
            .then((data) => {
                console.log("Carrito guardado en la sesión:", data);
            })
            .catch((error) => {
                console.error("Error al guardar el carrito:", error);
            });
    }
});
