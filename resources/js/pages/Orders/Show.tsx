import { useState } from 'react';
import { Head, router, usePage } from '@inertiajs/react';
import AppLayout from '@/layouts/app-layout';
import { type BreadcrumbItem } from '@/types';
import { Search, ChevronLeft, Loader2 } from 'lucide-react';
import { toast } from 'sonner';

interface OrderItem {
    id: number;
    food_id: number;
    food_name: string;
    description: string;
    price: number | string;
    quantity: number;
    subtotal: number | string;
    special_instructions?: string;
}

interface Order {
    id: number;
    table_id: number;
    table_number: string;
    status: 'open' | 'closed';
    total_amount: number | string;
    tax_amount: number | string | null;
    service_charge: number | string | null;
    items: OrderItem[];
    created_at: string;
    notes?: string;
}

interface Food {
    id: number;
    name: string;
    description: string;
    price: number | string;
    type: 'food' | 'beverage';
    is_available: boolean;
}

interface OrderDetailProps {
    order: Order;
    foods: Food[];
}
interface CartItem {
    food: Food;
    quantity: number;
}
const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Tables', href: '/tables' },
    { title: 'Order Detail', href: '#' },
];

export default function OrderDetail({ order, foods }: OrderDetailProps) {
    const { auth } = usePage<{ auth: { user: { roles: { name: string }[] } } }>().props;
    const isCashier = auth.user.roles.some(role => role.name === 'cashier');
    
    const [activeTab, setActiveTab] = useState<'food' | 'beverage'>('food');
    const [searchQuery, setSearchQuery] = useState('');
    const [cart, setCart] = useState<Map<number, CartItem>>(new Map());
    const [processing, setProcessing] = useState(false);
    const [closingOrder, setClosingOrder] = useState(false);

    const filteredFoods = foods.filter((food) => {
        const matchesSearch = food.name.toLowerCase().includes(searchQuery.toLowerCase());
        const matchesTab = food.type === activeTab;
        return matchesSearch && matchesTab && food.is_available;
    });

    // const addToCart = (foodId: number) => {
    //     const newCart = new Map(cart);
    //     newCart.set(foodId, (newCart.get(foodId) || 0) + 1);
    //     setCart(newCart);
    // };
    const addToCart = (food: Food) => {
        const newCart = new Map(cart);
        const existing = newCart.get(food.id);
        if (existing) {
            newCart.set(food.id, { food, quantity: existing.quantity + 1 });
        } else {
            newCart.set(food.id, { food, quantity: 1 });
        }
        setCart(newCart);
    };
    const updateQuantity = (foodId: number, quantity: number) => {
        if (quantity <= 0) {
            const newCart = new Map(cart);
            newCart.delete(foodId);
            setCart(newCart);
        } else {
            const newCart = new Map(cart);
             const item = newCart.get(foodId);
            // newCart.set(foodId, quantity);
            // setCart(newCart);
            if (item) {
                newCart.set(foodId, { ...item, quantity });
            }
            setCart(newCart);
        }
    };

    const handleAddItems = () => {
        if (cart.size === 0) {
            toast.error('Please add at least one item to the order');
            return;
        }

        setProcessing(true);
        const items = Array.from(cart.values()).map((item) => ({
            food_id: item.food.id,
            quantity: item.quantity,
        }));

        router.post(`/orders/${order.id}/items`, 
            { items },
            {
                preserveScroll: true,
                onError: (errors) => {
                    const errorMessages = Object.values(errors).flat().join(', ');
                    toast.error(`Failed to add items: ${errorMessages}`);
                    setProcessing(false);
                },
                onFinish: () => {
                    setCart(new Map());
                    setProcessing(false);
                },
            }
        );
    };

    const handleCloseOrder = () => {
        if (confirm('Are you sure you want to close this order? This action cannot be undone.')) {
            setClosingOrder(true);
            router.post(`/orders/${order.id}/close`, {}, {
                onError: () => {
                    toast.error('Failed to close order. Please try again.');
                    setClosingOrder(false);
                },
            });
        }
    };

    const parseNumber = (value: number | string | null | undefined): number => {
        if (value === null || value === undefined) return 0;
        return typeof value === 'string' ? parseFloat(value) : value;
    };

    const existingItemsSubtotal = order.items.reduce((sum, item) => sum + parseNumber(item.subtotal), 0);
    const cartItems = Array.from(cart.values());
    const cartSubtotal = cartItems.reduce((sum, item) => sum + (parseNumber(item.food.price) * item.quantity), 0);
    const subtotal = existingItemsSubtotal + cartSubtotal;
    const tax = subtotal * 0.1;
    const serviceCharge = subtotal * 0.05;
    const total = subtotal + tax + serviceCharge;

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title={`Order - Table ${order.table_number}`} />

            <div className="flex h-full flex-1 gap-6 p-6">
                {/* Left Panel - Menu */}
                <div className="flex-1 rounded-xl border bg-card shadow-sm">
                    <div className="border-b px-6 py-4">
                        <div className="mb-4 flex items-center gap-3">
                            <button
                                onClick={() => router.visit('/dashboard')}
                                className="flex h-8 w-8 items-center justify-center rounded-md border hover:bg-muted"
                            >
                                <ChevronLeft className="h-4 w-4" />
                            </button>
                            <div>
                                <h3 className="font-semibold">Table {order.table_number}</h3>
                                <p className="text-sm text-muted-foreground">Dine In</p>
                            </div>
                        </div>

                        {/* Tabs */}
                        <div className="flex gap-2 border-b">
                            <button
                                onClick={() => setActiveTab('food')}
                                className={`px-4 py-2 text-sm font-medium capitalize ${
                                    activeTab === 'food'
                                        ? 'border-b-2 border-primary text-primary'
                                        : 'text-muted-foreground hover:text-foreground'
                                }`}
                            >
                                Food
                            </button>
                            <button
                                onClick={() => setActiveTab('beverage')}
                                className={`px-4 py-2 text-sm font-medium capitalize ${
                                    activeTab === 'beverage'
                                        ? 'border-b-2 border-primary text-primary'
                                        : 'text-muted-foreground hover:text-foreground'
                                }`}
                            >
                                Beverages
                            </button>
                        </div>
                    </div>

                    {/* Search */}
                    <div className="border-b px-6 py-4">
                        <div className="relative">
                            <Search className="absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-muted-foreground" />
                            <input
                                type="text"
                                placeholder="Search menu items..."
                                value={searchQuery}
                                onChange={(e) => setSearchQuery(e.target.value)}
                                className="w-full rounded-md border bg-background py-2 pl-10 pr-4 text-sm focus:border-primary focus:outline-none focus:ring-1 focus:ring-primary"
                            />
                        </div>
                    </div>

                    {/* Menu Items */}
                    <div className="max-h-[calc(100vh-400px)] overflow-y-auto p-6">
                        <div className="space-y-4">
                            {filteredFoods.map((food) => (
                                <div
                                    key={food.id}
                                    className="flex items-center justify-between rounded-lg border bg-background p-4 hover:bg-muted/50"
                                >
                                    <div className="flex-1">
                                        <h4 className="font-medium">{food.name}</h4>
                                        <p className="text-sm text-muted-foreground">{food.description}</p>
                                        <p className="mt-1 font-semibold">
                                            ${parseNumber(food.price).toFixed(2)}
                                        </p>
                                    </div>
                                    <button
                                        onClick={() => addToCart(food)}
                                        disabled={isCashier}
                                        className="flex h-10 w-10 items-center justify-center rounded-lg bg-primary text-primary-foreground hover:bg-primary/90 disabled:opacity-50 disabled:cursor-not-allowed transition-all"
                                        type="button"
                                    >
                                        +
                                    </button>
                                </div>
                            ))}
                        </div>
                    </div>
                </div>

                {/* Right Panel - Order Summary */}
                <div className="w-96 rounded-xl border bg-card shadow-sm">
                    <div className="border-b px-6 py-4">
                        <div className="flex items-center justify-between">
                            <div>
                                <h3 className="font-semibold">Current Order</h3>
                                <p className="text-sm text-muted-foreground">
                                    Table {order.table_number} • {new Date(order.created_at).toLocaleDateString()}
                                </p>
                            </div>
                            <button className="rounded-md border px-3 py-1 text-sm hover:bg-muted">
                                Send Invoice
                            </button>
                        </div>
                    </div>

                    {/* Order Items */}
                    <div className="max-h-[calc(100vh-500px)] overflow-y-auto border-b p-6">
                        <div className="space-y-4">
                            {order.items.map((item) => (
                                <div key={item.id} className="space-y-2">
                                    <div className="flex items-start justify-between">
                                        <div className="flex-1">
                                            <h4 className="font-medium">{item.food_name}</h4>
                                            <p className="text-sm text-muted-foreground">{item.description}</p>
                                        </div>
                                        <span className="font-semibold">${parseNumber(item.subtotal).toFixed(2)}</span>
                                    </div>
                                    <div className="flex items-center gap-2">
                                        <button className="flex h-6 w-6 items-center justify-center rounded border text-sm">
                                            -
                                        </button>
                                        <span className="w-8 text-center text-sm">{item.quantity}</span>
                                        <button className="flex h-6 w-6 items-center justify-center rounded border text-sm">
                                            +
                                        </button>
                                        <button className="ml-auto text-sm text-muted-foreground hover:text-destructive">
                                            ✕
                                        </button>
                                    </div>
                                </div>
                            ))}

                            {/* Cart Items (new items to add) */}
                            {Array.from(cart.entries()).map(([foodId, cartItem]) => {
                                return (
                                    <div key={foodId} className="space-y-2 border-t pt-4">
                                        <div className="flex items-start justify-between">
                                            <div className="flex-1">
                                                <h4 className="font-medium">{cartItem.food.name}</h4>
                                                <p className="text-sm text-muted-foreground">{cartItem.food.description}</p>
                                            </div>
                                            <span className="font-semibold">
                                                ${(parseNumber(cartItem.food.price) * cartItem.quantity).toFixed(2)}
                                            </span>
                                        </div>
                                        <div className="flex items-center gap-2">
                                            <button
                                                onClick={() => updateQuantity(foodId, cartItem.quantity - 1)}
                                                className="flex h-6 w-6 items-center justify-center rounded border text-sm"
                                            >
                                                -
                                            </button>
                                            <span className="w-8 text-center text-sm">{cartItem.quantity}</span>
                                            <button
                                                onClick={() => updateQuantity(foodId, cartItem.quantity + 1)}
                                                className="flex h-6 w-6 items-center justify-center rounded border text-sm"
                                            >
                                                +
                                            </button>
                                            <button
                                                onClick={() => updateQuantity(foodId, 0)}
                                                className="ml-auto text-sm text-muted-foreground hover:text-destructive"
                                            >
                                                ✕
                                            </button>
                                        </div>
                                    </div>
                                );
                            })}
                        </div>
                    </div>

                    {/* Total */}
                    <div className="space-y-3 border-b p-6">
                        <div className="flex justify-between text-sm">
                            <span className="text-muted-foreground">Subtotal</span>
                            <span className="font-medium">${subtotal.toFixed(2)}</span>
                        </div>
                        <div className="flex justify-between text-sm">
                            <span className="text-muted-foreground">Tax (10%)</span>
                            <span className="font-medium">${tax.toFixed(2)}</span>
                        </div>
                        <div className="flex justify-between text-sm">
                            <span className="text-muted-foreground">Service Charge (5%)</span>
                            <span className="font-medium">${serviceCharge.toFixed(2)}</span>
                        </div>
                        <div className="flex justify-between border-t pt-3 text-lg font-bold">
                            <span>Total</span>
                            <span>${total.toFixed(2)}</span>
                        </div>
                    </div>

                    {/* Actions */}
                    <div className="space-y-2 p-6">
                        {order.status === 'open' && (
                            <>
                                {cart.size === 0 && !isCashier && (
                                    <p className="text-xs text-center text-muted-foreground mb-2">
                                        Add items from the menu to enable this button
                                    </p>
                                )}
                                <button
                                    onClick={handleAddItems}
                                    disabled={cart.size === 0 || isCashier || processing}
                                    className="w-full rounded-md bg-primary py-2.5 text-sm font-medium text-primary-foreground hover:bg-primary/90 disabled:cursor-not-allowed disabled:opacity-50 transition-all flex items-center justify-center gap-2"
                                    type="button"
                                >
                                    {processing && <Loader2 className="h-4 w-4 animate-spin" />}
                                    {processing ? 'Adding Items...' : `Add Items to Order ${cart.size > 0 ? `(${cart.size})` : ''}`}
                                </button>
                                <button
                                    onClick={handleCloseOrder}
                                    disabled={closingOrder || processing}
                                    className="w-full rounded-md bg-green-600 py-2.5 text-sm font-medium text-white hover:bg-green-700 transition-all disabled:opacity-50 disabled:cursor-not-allowed flex items-center justify-center gap-2"
                                    type="button"
                                >
                                    {closingOrder && <Loader2 className="h-4 w-4 animate-spin" />}
                                    {closingOrder ? 'Closing Order...' : 'Close Order'}
                                </button>
                            </>
                        )}
                    </div>
                </div>
            </div>
        </AppLayout>
    );
}
