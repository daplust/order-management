import { useState } from 'react';
import { Head, router } from '@inertiajs/react';
import AppLayout from '@/layouts/app-layout';
import { type BreadcrumbItem } from '@/types';
import { Search, ChevronLeft, Loader2 } from 'lucide-react';
import { toast } from 'sonner';

interface Food {
    id: number;
    name: string;
    description: string;
    price: number | string;
    type: 'food' | 'beverage';
    is_available: boolean;
}

interface Table {
    id: number;
    table_number: string;
    capacity: number;
}

interface CreateOrderProps {
    table: Table;
    foods: Food[];
}

interface CartItem {
    food: Food;
    quantity: number;
}

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Tables', href: '/tables' },
    { title: 'New Order', href: '#' },
];

export default function CreateOrder({ table, foods }: CreateOrderProps) {
    const [activeTab, setActiveTab] = useState<'food' | 'beverage'>('food');
    const [searchQuery, setSearchQuery] = useState('');
    const [cart, setCart] = useState<Map<number, CartItem>>(new Map());
    const [processing, setProcessing] = useState(false);

    const filteredFoods = foods.filter((food) => {
        const matchesSearch = food.name.toLowerCase().includes(searchQuery.toLowerCase());
        const matchesTab = food.type === activeTab;
        return matchesSearch && matchesTab && food.is_available;
    });

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
            if (item) {
                newCart.set(foodId, { ...item, quantity });
            }
            setCart(newCart);
        }
    };

    const handleCreateOrder = () => {
        if (cart.size === 0) {
            toast.error('Please add at least one item to the order');
            return;
        }

        setProcessing(true);
        const items = Array.from(cart.values()).map((item) => ({
            food_id: item.food.id,
            quantity: item.quantity,
        }));

        router.post('/orders', {
            table_id: table.id,
            items,
        }, {
            onError: () => {
                toast.error('Failed to create order. Please try again.');
                setProcessing(false);
            },
            onFinish: () => {
                setProcessing(false);
            },
        });
    };

    const parseNumber = (value: number | string | null | undefined): number => {
        if (value === null || value === undefined) return 0;
        return typeof value === 'string' ? parseFloat(value) : value;
    };

    const cartItems = Array.from(cart.values());
    const subtotal = cartItems.reduce((sum, item) => sum + parseNumber(item.food.price) * item.quantity, 0);
    const tax = subtotal * 0.1;
    const serviceCharge = subtotal * 0.05;
    const total = subtotal + tax + serviceCharge;

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title={`New Order - Table ${table.table_number}`} />

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
                                <h3 className="font-semibold">Table {table.table_number}</h3>
                                <p className="text-sm text-muted-foreground">New Order</p>
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
                        <div className="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                            {filteredFoods.map((food) => (
                                <div
                                    key={food.id}
                                    className="flex flex-col justify-between rounded-lg border bg-background p-4 hover:bg-muted/50"
                                >
                                    <div className="mb-3">
                                        <h4 className="font-medium">{food.name}</h4>
                                        <p className="mt-1 text-sm text-muted-foreground">{food.description}</p>
                                    </div>
                                    <div className="flex items-center justify-between">
                                        <p className="font-semibold">${parseNumber(food.price).toFixed(2)}</p>
                                        <button
                                            onClick={() => addToCart(food)}
                                            className="flex h-8 w-8 items-center justify-center rounded-lg bg-primary text-primary-foreground hover:bg-primary/90"
                                        >
                                            +
                                        </button>
                                    </div>
                                </div>
                            ))}
                        </div>
                    </div>
                </div>

                {/* Right Panel - Cart */}
                <div className="w-96 rounded-xl border bg-card shadow-sm">
                    <div className="border-b px-6 py-4">
                        <h3 className="font-semibold">New Order</h3>
                        <p className="text-sm text-muted-foreground">Table {table.table_number}</p>
                    </div>

                    {/* Cart Items */}
                    <div className="max-h-[calc(100vh-400px)] overflow-y-auto border-b p-6">
                        {cartItems.length === 0 ? (
                            <div className="py-12 text-center text-sm text-muted-foreground">
                                No items added yet
                            </div>
                        ) : (
                            <div className="space-y-4">
                                {cartItems.map((item) => (
                                    <div key={item.food.id} className="space-y-2">
                                        <div className="flex items-start justify-between">
                                            <div className="flex-1">
                                                <h4 className="font-medium">{item.food.name}</h4>
                                                <p className="text-sm text-muted-foreground">
                                                    {item.food.description}
                                                </p>
                                            </div>
                                            <span className="font-semibold">
                                                ${(parseNumber(item.food.price) * item.quantity).toFixed(2)}
                                            </span>
                                        </div>
                                        <div className="flex items-center gap-2">
                                            <button
                                                onClick={() => updateQuantity(item.food.id, item.quantity - 1)}
                                                className="flex h-6 w-6 items-center justify-center rounded border text-sm hover:bg-muted"
                                            >
                                                -
                                            </button>
                                            <span className="w-8 text-center text-sm">{item.quantity}</span>
                                            <button
                                                onClick={() => updateQuantity(item.food.id, item.quantity + 1)}
                                                className="flex h-6 w-6 items-center justify-center rounded border text-sm hover:bg-muted"
                                            >
                                                +
                                            </button>
                                            <button
                                                onClick={() => updateQuantity(item.food.id, 0)}
                                                className="ml-auto text-sm text-muted-foreground hover:text-destructive"
                                            >
                                                ✕
                                            </button>
                                        </div>
                                    </div>
                                ))}
                            </div>
                        )}
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
                        <button
                            onClick={handleCreateOrder}
                            disabled={cartItems.length === 0 || processing}
                            className="w-full rounded-md bg-primary py-2.5 text-sm font-medium text-primary-foreground hover:bg-primary/90 disabled:cursor-not-allowed disabled:opacity-50 flex items-center justify-center gap-2"
                        >
                            {processing && <Loader2 className="h-4 w-4 animate-spin" />}
                            {processing ? 'Creating Order...' : 'Create Order'}
                        </button>
                        <button
                            onClick={() => router.visit('/tables')}
                            disabled={processing}
                            className="w-full rounded-md border py-2.5 text-sm font-medium hover:bg-muted disabled:opacity-50 disabled:cursor-not-allowed"
                        >
                            Cancel
                        </button>
                    </div>
                </div>
            </div>
        </AppLayout>
    );
}
