import { Head, router, usePage } from '@inertiajs/react';
import AppLayout from '@/layouts/app-layout';
import { type BreadcrumbItem } from '@/types';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Eye, Receipt, Plus, ArrowLeft } from 'lucide-react';

interface Order {
    id: number;
    table_number: string;
    status: 'open' | 'closed';
    total_amount: number | string;
    created_at: string;
    items_count: number;
}

interface OrdersIndexProps {
    orders: Order[];
}

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Orders', href: '#' },
];

export default function OrdersIndex({ orders }: OrdersIndexProps) {
    const { auth } = usePage<{ auth: { user: { roles: { name: string }[] } } }>().props;
    const isWaiter = auth.user.roles.some(role => role.name === 'waiter');

    const formatDate = (dateString: string) => {
        return new Date(dateString).toLocaleString('en-US', {
            month: 'short',
            day: 'numeric',
            year: 'numeric',
            hour: '2-digit',
            minute: '2-digit',
        });
    };

    const formatCurrency = (amount: number | string | null | undefined) => {
        if (amount === null || amount === undefined) return '$0.00';
        const numAmount = typeof amount === 'string' ? parseFloat(amount) : amount;
        return `$${numAmount.toFixed(2)}`;
    };

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Orders" />

            <div className="space-y-6 p-6">
                {/* Header */}
                <div className="flex items-center justify-between">
                    <div>
                        <h1 className="text-3xl font-bold tracking-tight">Orders</h1>
                        <p className="text-muted-foreground">
                            Manage and view all restaurant orders
                        </p>
                    </div>
                    <div className="flex gap-2">
                        <Button
                            variant="outline"
                            onClick={() => router.visit('/dashboard')}
                        >
                            <ArrowLeft className="mr-2 h-4 w-4" />
                            Back to Tables
                        </Button>
                        {isWaiter && (
                            <Button
                                onClick={() => router.visit('/dashboard')}
                            >
                                <Plus className="mr-2 h-4 w-4" />
                                New Order
                            </Button>
                        )}
                    </div>
                </div>

                {/* Orders Table */}
                <Card>
                    <CardHeader>
                        <CardTitle>All Orders</CardTitle>
                    </CardHeader>
                    <CardContent>
                        <div className="overflow-x-auto">
                            <table className="w-full">
                                <thead className="border-b">
                                    <tr className="text-left text-sm font-medium text-muted-foreground">
                                        <th className="pb-3">Order ID</th>
                                        <th className="pb-3">Table</th>
                                        <th className="pb-3">Items</th>
                                        <th className="pb-3">Total Amount</th>
                                        <th className="pb-3">Status</th>
                                        <th className="pb-3">Created At</th>
                                        <th className="pb-3 text-right">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    {orders.length === 0 ? (
                                        <tr>
                                            <td colSpan={7} className="py-12 text-center text-muted-foreground">
                                                No orders found
                                            </td>
                                        </tr>
                                    ) : (
                                        orders.map((order) => (
                                            <tr key={order.id} className="border-b last:border-0">
                                                <td className="py-4 font-medium">
                                                    #{order.id.toString().padStart(4, '0')}
                                                </td>
                                                <td className="py-4">{order.table_number}</td>
                                                <td className="py-4">{order.items_count} items</td>
                                                <td className="py-4 font-semibold">
                                                    {formatCurrency(order.total_amount)}
                                                </td>
                                                <td className="py-4">
                                                    <Badge
                                                        variant={order.status === 'open' ? 'default' : 'secondary'}
                                                    >
                                                        {order.status}
                                                    </Badge>
                                                </td>
                                                <td className="py-4 text-muted-foreground">
                                                    {formatDate(order.created_at)}
                                                </td>
                                                <td className="py-4 text-right">
                                                    <div className="flex items-center justify-end gap-2">
                                                        <Button
                                                            size="sm"
                                                            variant="outline"
                                                            onClick={() => router.visit(`/orders/${order.id}`)}
                                                        >
                                                            <Eye className="mr-1 h-4 w-4" />
                                                            View
                                                        </Button>
                                                        {order.status === 'closed' && (
                                                            <Button
                                                                size="sm"
                                                                variant="outline"
                                                                onClick={() => router.visit(`/orders/${order.id}/receipt`)}
                                                            >
                                                                <Receipt className="mr-1 h-4 w-4" />
                                                                Receipt
                                                            </Button>
                                                        )}
                                                    </div>
                                                </td>
                                            </tr>
                                        ))
                                    )}
                                </tbody>
                            </table>
                        </div>
                    </CardContent>
                </Card>

                {/* Summary */}
                {orders.length > 0 && (
                    <div className="grid gap-4 md:grid-cols-3">
                        <div className="rounded-lg border bg-card p-6 shadow-sm">
                            <div className="text-sm font-medium text-muted-foreground">
                                Total Orders
                            </div>
                            <div className="mt-2 text-2xl font-bold">{orders.length}</div>
                        </div>
                        <div className="rounded-lg border bg-card p-6 shadow-sm">
                            <div className="text-sm font-medium text-muted-foreground">
                                Open Orders
                            </div>
                            <div className="mt-2 text-2xl font-bold">
                                {orders.filter(o => o.status === 'open').length}
                            </div>
                        </div>
                        <div className="rounded-lg border bg-card p-6 shadow-sm">
                            <div className="text-sm font-medium text-muted-foreground">
                                Closed Orders
                            </div>
                            <div className="mt-2 text-2xl font-bold">
                                {orders.filter(o => o.status === 'closed').length}
                            </div>
                        </div>
                    </div>
                )}
            </div>
        </AppLayout>
    );
}
