import { useState } from 'react';
import { Head, router } from '@inertiajs/react';
import AppLayout from '@/layouts/app-layout';
import { type BreadcrumbItem } from '@/types';
import { Search } from 'lucide-react';

interface Table {
    id: number;
    table_number: string;
    capacity: number;
    is_available: boolean;
    current_order?: {
        id: number;
        total_amount: number;
    };
}

interface TablesProps {
    tables: Table[];
    stats: {
        totalTables: number;
        availableTables: number;
        occupiedTables: number;
        runningOrders: number;
    };
}

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Table List', href: '/tables' },
];

export default function Tables({ tables, stats }: TablesProps) {
    const [filterStatus, setFilterStatus] = useState<string>('all');
    const [searchQuery, setSearchQuery] = useState('');
    const [viewMode, setViewMode] = useState<'floor' | 'list'>('floor');

    const filteredTables = tables.filter((table) => {
        const matchesSearch = table.table_number.toLowerCase().includes(searchQuery.toLowerCase());
        const matchesFilter =
            filterStatus === 'all' ||
            (filterStatus === 'available' && table.is_available) ||
            (filterStatus === 'occupied' && !table.is_available);
        return matchesSearch && matchesFilter;
    });

    const handleTableClick = (table: Table) => {
        if (table.current_order) {
            router.visit(`/orders/${table.current_order.id}`);
        } else {
            router.visit(`/orders/create?table_id=${table.id}`);
        }
    };

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Table List" />

            <div className="flex h-full flex-1 flex-col gap-6 p-6">
                {/* Header Card */}
                <div className="rounded-xl border bg-card shadow-sm">
                    <div className="border-b bg-muted/50 px-6 py-4">
                        <h3 className="text-lg font-semibold">RestaurantPOS</h3>
                    </div>

                    {/* Search Bar */}
                    <div className="border-b px-6 py-4">
                        <div className="relative max-w-xs">
                            <Search className="absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-muted-foreground" />
                            <input
                                type="text"
                                placeholder="Search table..."
                                value={searchQuery}
                                onChange={(e) => setSearchQuery(e.target.value)}
                                className="w-full rounded-md border bg-background py-2 pl-10 pr-4 text-sm focus:border-primary focus:outline-none focus:ring-1 focus:ring-primary"
                            />
                        </div>
                    </div>

                    {/* Table Management */}
                    <div className="px-6 py-6">
                        <div className="mb-6 flex items-center justify-between">
                            <h4 className="text-base font-semibold">Table Management</h4>
                            <div className="flex gap-2">
                                <button
                                    onClick={() => setViewMode('floor')}
                                    className={`rounded-md px-4 py-2 text-sm font-medium transition-colors ${
                                        viewMode === 'floor'
                                            ? 'bg-primary text-primary-foreground'
                                            : 'border bg-background text-foreground hover:bg-muted'
                                    }`}
                                >
                                    Floor Plan
                                </button>
                                <button
                                    onClick={() => setViewMode('list')}
                                    className={`rounded-md px-4 py-2 text-sm font-medium transition-colors ${
                                        viewMode === 'list'
                                            ? 'bg-primary text-primary-foreground'
                                            : 'border bg-background text-foreground hover:bg-muted'
                                    }`}
                                >
                                    List View
                                </button>
                            </div>
                        </div>

                        {/* Status Filters */}
                        <div className="mb-6">
                            <p className="mb-3 text-sm font-medium">Table Status</p>
                            <div className="flex flex-wrap gap-3">
                                <label className="flex cursor-pointer items-center gap-2">
                                    <input
                                        type="radio"
                                        name="status"
                                        checked={filterStatus === 'all'}
                                        onChange={() => setFilterStatus('all')}
                                        className="h-4 w-4 text-primary focus:ring-primary"
                                    />
                                    <span className="text-sm">All Tables</span>
                                </label>
                                <label className="flex cursor-pointer items-center gap-2">
                                    <input
                                        type="radio"
                                        name="status"
                                        checked={filterStatus === 'occupied'}
                                        onChange={() => setFilterStatus('occupied')}
                                        className="h-4 w-4 text-primary focus:ring-primary"
                                    />
                                    <span className="text-sm">Occupied</span>
                                </label>
                                <label className="flex cursor-pointer items-center gap-2">
                                    <input
                                        type="radio"
                                        name="status"
                                        checked={filterStatus === 'available'}
                                        onChange={() => setFilterStatus('available')}
                                        className="h-4 w-4 text-primary focus:ring-primary"
                                    />
                                    <span className="text-sm">Available</span>
                                </label>
                            </div>
                        </div>

                        {/* Tables Grid */}
                        <div className="grid grid-cols-3 gap-3 sm:grid-cols-4 md:grid-cols-6 lg:grid-cols-8">
                            {filteredTables.map((table) => (
                                <button
                                    key={table.id}
                                    onClick={() => handleTableClick(table)}
                                    className={`flex aspect-square items-center justify-center rounded-lg text-lg font-semibold transition-all hover:scale-105 ${
                                        table.is_available
                                            ? 'bg-muted text-muted-foreground hover:bg-muted/80'
                                            : 'bg-slate-600 text-white hover:bg-slate-700'
                                    }`}
                                >
                                    {table.table_number}
                                </button>
                            ))}
                        </div>
                    </div>
                </div>

                {/* Quick Stats */}
                <div className="rounded-xl border bg-card shadow-sm">
                    <div className="px-6 py-4">
                        <h4 className="mb-4 text-base font-semibold">Quick Stats</h4>
                        <div className="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                            <div className="rounded-lg border bg-background p-4">
                                <div className="text-3xl font-bold">{stats.totalTables}</div>
                                <div className="text-sm text-muted-foreground">Available Tables</div>
                            </div>
                            <div className="rounded-lg border bg-background p-4">
                                <div className="text-3xl font-bold">{stats.occupiedTables}</div>
                                <div className="text-sm text-muted-foreground">Occupied Tables</div>
                            </div>
                            <div className="rounded-lg border bg-background p-4">
                                <div className="text-3xl font-bold">{stats.runningOrders}</div>
                                <div className="text-sm text-muted-foreground">Running Tables</div>
                            </div>
                            <div className="rounded-lg border bg-background p-4">
                                <div className="text-3xl font-bold">{stats.availableTables}</div>
                                <div className="text-sm text-muted-foreground">Vacant Tables</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </AppLayout>
    );
}
