import { useState } from 'react';
import { Head, router } from '@inertiajs/react';
import AppLayout from '@/layouts/app-layout';
import { type BreadcrumbItem } from '@/types';
import { Search, Plus, Pencil, Trash2 } from 'lucide-react';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Badge } from '@/components/ui/badge';
import { toast } from 'sonner';

interface Food {
    id: number;
    name: string;
    description: string;
    price: number | string;
    type: 'food' | 'beverage';
    is_available: boolean;
}

interface FoodsIndexProps {
    foods: Food[];
}

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Foods', href: '#' },
];

export default function FoodsIndex({ foods }: FoodsIndexProps) {
    const [searchQuery, setSearchQuery] = useState('');
    const [activeTab, setActiveTab] = useState<'all' | 'food' | 'beverage'>('all');

    const formatCurrency = (value: number | string | null | undefined): string => {
        if (value === null || value === undefined) return '$0.00';
        const numValue = typeof value === 'string' ? parseFloat(value) : value;
        return `$${numValue.toFixed(2)}`;
    };

    const filteredFoods = foods.filter((food) => {
        const matchesSearch = food.name.toLowerCase().includes(searchQuery.toLowerCase()) ||
                            food.description?.toLowerCase().includes(searchQuery.toLowerCase());
        const matchesTab = activeTab === 'all' || food.type === activeTab;
        return matchesSearch && matchesTab;
    });

    const handleDelete = (id: number, name: string) => {
        if (confirm(`Are you sure you want to delete "${name}"?`)) {
            router.delete(`/foods/${id}`, {
                onError: () => {
                    toast.error('Failed to delete food item.');
                },
            });
        }
    };

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Food Menu" />

            <div className="space-y-6">
                {/* Header */}
                <div className="flex items-center justify-between">
                    <div>
                        <h2 className="text-2xl font-bold tracking-tight">Food Menu</h2>
                        <p className="text-muted-foreground">
                            Manage your restaurant's menu items
                        </p>
                    </div>
                    <Button onClick={() => router.visit('/foods/create')}>
                        <Plus className="mr-2 h-4 w-4" />
                        Add Item
                    </Button>
                </div>

                {/* Search and Filters */}
                <Card>
                    <CardContent className="pt-6">
                        <div className="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
                            <div className="relative flex-1 max-w-sm">
                                <Search className="absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-muted-foreground" />
                                <Input
                                    placeholder="Search menu items..."
                                    value={searchQuery}
                                    onChange={(e) => setSearchQuery(e.target.value)}
                                    className="pl-9"
                                />
                            </div>
                            <div className="flex gap-2">
                                <Button
                                    variant={activeTab === 'all' ? 'default' : 'outline'}
                                    onClick={() => setActiveTab('all')}
                                    size="sm"
                                >
                                    All
                                </Button>
                                <Button
                                    variant={activeTab === 'food' ? 'default' : 'outline'}
                                    onClick={() => setActiveTab('food')}
                                    size="sm"
                                >
                                    Food
                                </Button>
                                <Button
                                    variant={activeTab === 'beverage' ? 'default' : 'outline'}
                                    onClick={() => setActiveTab('beverage')}
                                    size="sm"
                                >
                                    Beverages
                                </Button>
                            </div>
                        </div>
                    </CardContent>
                </Card>

                {/* Foods Grid */}
                <div className="grid gap-4 md:grid-cols-2 lg:grid-cols-3">
                    {filteredFoods.map((food) => (
                        <Card key={food.id}>
                            <CardHeader>
                                <div className="flex items-start justify-between">
                                    <div className="flex-1">
                                        <CardTitle className="text-lg">{food.name}</CardTitle>
                                        <div className="mt-2 flex gap-2">
                                            <Badge variant={food.type === 'food' ? 'default' : 'secondary'}>
                                                {food.type}
                                            </Badge>
                                            <Badge variant={food.is_available ? 'outline' : 'destructive'}>
                                                {food.is_available ? 'Available' : 'Unavailable'}
                                            </Badge>
                                        </div>
                                    </div>
                                </div>
                            </CardHeader>
                            <CardContent>
                                <p className="text-sm text-muted-foreground mb-4">
                                    {food.description}
                                </p>
                                <div className="flex items-center justify-between">
                                    <span className="text-2xl font-bold">
                                        {formatCurrency(food.price)}
                                    </span>
                                    <div className="flex gap-2">
                                        <Button
                                            variant="outline"
                                            size="sm"
                                            onClick={() => router.visit(`/foods/${food.id}/edit`)}
                                        >
                                            <Pencil className="h-4 w-4" />
                                        </Button>
                                        <Button
                                            variant="outline"
                                            size="sm"
                                            onClick={() => handleDelete(food.id, food.name)}
                                        >
                                            <Trash2 className="h-4 w-4" />
                                        </Button>
                                    </div>
                                </div>
                            </CardContent>
                        </Card>
                    ))}
                </div>

                {filteredFoods.length === 0 && (
                    <Card>
                        <CardContent className="py-12">
                            <div className="text-center">
                                <p className="text-muted-foreground">No menu items found</p>
                            </div>
                        </CardContent>
                    </Card>
                )}
            </div>
        </AppLayout>
    );
}
