import { computed, onMounted, ref, watch } from 'vue';
import { useFormatter } from './useFormatter';

type CartItem = {
    id: string;
    name: string;
    price: number;
    quantity: number;
    image?: string;
};

type Cart = {
    items: CartItem[];
};

const CART_STORAGE_KEY = 'cart';

export function useCart() {
    const { formatRupiah, formatRupiahSimple } = useFormatter();

    // Reactive cart state
    const cart = ref<Cart>({ items: [] });

    // Load cart from localStorage on mount
    onMounted(() => {
        loadCartFromStorage();
    });

    // Watch cart changes and save to localStorage
    watch(
        () => cart.value,
        (newCart) => {
            saveCartToStorage(newCart);
        },
        { deep: true },
    );

    // Computed properties
    const cartItemsCount = computed(() => {
        return cart.value.items.reduce((total, item) => total + item.quantity, 0);
    });

    const cartTotal = computed(() => {
        return cart.value.items.reduce((total, item) => total + item.price * item.quantity, 0);
    });

    const formattedCartTotal = computed(() => {
        return formatRupiah(cartTotal.value);
    });

    const isEmpty = computed(() => {
        return cart.value.items.length === 0;
    });

    // Cart management functions
    const loadCartFromStorage = () => {
        try {
            const storedCart = localStorage.getItem(CART_STORAGE_KEY);
            if (storedCart == undefined) {
                localStorage.setItem(CART_STORAGE_KEY, JSON.stringify({ items: [] }));
            }
            if (storedCart) {
                cart.value = JSON.parse(storedCart);
            }
        } catch (error) {
            console.error('Error loading cart from storage:', error);
            cart.value = { items: [] };
        }
    };

    const saveCartToStorage = (cartData: Cart) => {
        try {
            localStorage.setItem(CART_STORAGE_KEY, JSON.stringify(cartData));
        } catch (error) {
            console.error('Error saving cart to storage:', error);
        }
    };

    const addToCart = (
        product: {
            id: string;
            name: string;
            price: number;
            image?: string;
        },
        quantity: number = 1,
    ) => {
        const existingItemIndex = cart.value.items.findIndex((item) => item.id === product.id);

        if (existingItemIndex > -1) {
            // Update existing item quantity
            cart.value.items[existingItemIndex].quantity += quantity;
        } else {
            // Add new item to cart
            cart.value.items.push({
                id: product.id,
                name: product.name,
                price: product.price,
                quantity,
                image: product.image,
            });
        }
    };

    const removeFromCart = (id: string) => {
        cart.value.items = cart.value.items.filter((item) => item.id !== id);
    };

    const updateQuantity = (id: string, quantity: number) => {
        if (quantity <= 0) {
            removeFromCart(id);
            return;
        }

        const itemIndex = cart.value.items.findIndex((item) => item.id === id);
        if (itemIndex > -1) {
            cart.value.items[itemIndex].quantity = quantity;
        }
    };

    const clearCart = () => {
        cart.value.items = [];
    };

    const getItemQuantity = (id: string): number => {
        const item = cart.value.items.find((item) => item.id === id);
        return item ? item.quantity : 0;
    };

    const isInCart = (id: string): boolean => {
        return cart.value.items.some((item) => item.id === id);
    };

    return {
        // State
        cart: cart,
        cartItems: computed(() => cart.value.items),
        cartItemsCount,
        cartTotal,
        formattedCartTotal,
        isEmpty,

        // Actions
        addToCart,
        removeFromCart,
        updateQuantity,
        clearCart,
        getItemQuantity,
        isInCart,

        // Formatters (re-exported for convenience)
        formatRupiah,
        formatRupiahSimple,
    };
}

export function useCheckout() {
    const { formatRupiah, formatRupiahSimple } = useFormatter();
    const cartComposable = useCart();

    // Checkout-specific state
    const isProcessingCheckout = ref(false);
    const checkoutError = ref<string | null>(null);

    // Customer information
    const customerInfo = ref({
        name: '',
        email: '',
        phone: '',
        address: '',
    });

    // Payment method
    const paymentMethod = ref<'cash' | 'transfer' | 'card'>('cash');

    // Checkout functions
    const validateCheckout = (): boolean => {
        if (cartComposable.isEmpty.value) {
            checkoutError.value = 'Cart is empty';
            return false;
        }

        if (!customerInfo.value.name.trim()) {
            checkoutError.value = 'Customer name is required';
            return false;
        }

        if (!customerInfo.value.phone.trim()) {
            checkoutError.value = 'Phone number is required';
            return false;
        }

        checkoutError.value = null;
        return true;
    };

    const processCheckout = async () => {
        if (!validateCheckout()) {
            return false;
        }

        isProcessingCheckout.value = true;
        checkoutError.value = null;

        try {
            // Here you would typically make an API call to process the order
            // For now, we'll simulate the process
            await new Promise((resolve) => setTimeout(resolve, 2000));

            // Clear cart after successful checkout
            cartComposable.clearCart();

            // Reset checkout state
            customerInfo.value = {
                name: '',
                email: '',
                phone: '',
                address: '',
            };

            return true;
        } catch (error) {
            checkoutError.value = error instanceof Error ? error.message : 'Checkout failed';
            return false;
        } finally {
            isProcessingCheckout.value = false;
        }
    };

    return {
        // Cart functionality
        ...cartComposable,

        // Checkout state
        isProcessingCheckout,
        checkoutError,
        customerInfo,
        paymentMethod,

        // Checkout actions
        validateCheckout,
        processCheckout,

        // Formatters
        formatRupiah,
        formatRupiahSimple,
    };
}
