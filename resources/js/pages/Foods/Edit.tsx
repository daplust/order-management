import { FormEvent, useState } from 'react';
import { Head, router } from '@inertiajs/react';
import AppLayout from '@/layouts/app-layout';
import { type BreadcrumbItem } from '@/types';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import { ArrowLeft, Loader2 } from 'lucide-react';
import { toast } from 'sonner';

interface Food {
    id: number;
    name: string;
    description: string;
    price: number | string;
    type: 'food' | 'beverage';
    is_available: boolean;
}

interface EditFoodProps {
    food: Food;
}

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Foods', href: '/foods' },
    { title: 'Edit', href: '#' },
];

export default function EditFood({ food }: EditFoodProps) {
    const [formData, setFormData] = useState({
        name: food.name,
        description: food.description || '',
        price: food.price.toString(),
        type: food.type,
        is_available: food.is_available,
    });

    const [errors, setErrors] = useState<Record<string, string>>({});
    const [processing, setProcessing] = useState(false);

    const handleSubmit = (e: FormEvent) => {
        e.preventDefault();
        setProcessing(true);

        router.put(`/foods/${food.id}`, formData, {
            onError: (errors) => {
                setErrors(errors);
                setProcessing(false);
                toast.error('Failed to update food item. Please check the form.');
            },
            onFinish: () => {
                setProcessing(false);
            },
        });
    };

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title={`Edit ${food.name}`} />

            <div className="space-y-6 p-6">
                {/* Header */}
                <div className="flex items-center justify-between">
                    <div className="flex items-center gap-4">
                        <Button
                            variant="outline"
                            size="sm"
                            onClick={() => router.visit('/foods')}
                        >
                            <ArrowLeft className="mr-2 h-4 w-4" />
                            Back to Foods
                        </Button>
                        <div>
                            <h1 className="text-3xl font-bold tracking-tight">Edit Food Item</h1>
                            <p className="text-muted-foreground">Update {food.name}</p>
                        </div>
                    </div>
                </div>

                {/* Form */}
                <Card className="mx-auto max-w-2xl">
                    <CardHeader>
                        <CardTitle>Food Information</CardTitle>
                    </CardHeader>
                    <CardContent>
                        <form onSubmit={handleSubmit} className="space-y-6">
                            {/* Name */}
                            <div className="space-y-2">
                                <Label htmlFor="name">Name *</Label>
                                <Input
                                    id="name"
                                    type="text"
                                    value={formData.name}
                                    onChange={(e) => setFormData({ ...formData, name: e.target.value })}
                                    placeholder="e.g., Burger, Coffee"
                                    className={errors.name ? 'border-red-500' : ''}
                                />
                                {errors.name && (
                                    <p className="text-sm text-red-500">{errors.name}</p>
                                )}
                            </div>

                            {/* Description */}
                            <div className="space-y-2">
                                <Label htmlFor="description">Description</Label>
                                <Textarea
                                    id="description"
                                    value={formData.description}
                                    onChange={(e: React.ChangeEvent<HTMLTextAreaElement>) => setFormData({ ...formData, description: e.target.value })}
                                    placeholder="Describe the item..."
                                    rows={3}
                                    className={errors.description ? 'border-red-500' : ''}
                                />
                                {errors.description && (
                                    <p className="text-sm text-red-500">{errors.description}</p>
                                )}
                            </div>

                            {/* Price */}
                            <div className="space-y-2">
                                <Label htmlFor="price">Price *</Label>
                                <div className="relative">
                                    <span className="absolute left-3 top-1/2 -translate-y-1/2 text-muted-foreground">$</span>
                                    <Input
                                        id="price"
                                        type="number"
                                        step="0.01"
                                        min="0"
                                        value={formData.price}
                                        onChange={(e) => setFormData({ ...formData, price: e.target.value })}
                                        placeholder="0.00"
                                        className={`pl-7 ${errors.price ? 'border-red-500' : ''}`}
                                    />
                                </div>
                                {errors.price && (
                                    <p className="text-sm text-red-500">{errors.price}</p>
                                )}
                            </div>

                            {/* Type */}
                            <div className="space-y-2">
                                <Label>Type *</Label>
                                <div className="flex gap-4">
                                    <label className="flex items-center gap-2 cursor-pointer">
                                        <input
                                            type="radio"
                                            name="type"
                                            value="food"
                                            checked={formData.type === 'food'}
                                            onChange={(e) => setFormData({ ...formData, type: e.target.value as 'food' | 'beverage' })}
                                            className="h-4 w-4 text-primary focus:ring-primary"
                                        />
                                        <span className="text-sm">Food</span>
                                    </label>
                                    <label className="flex items-center gap-2 cursor-pointer">
                                        <input
                                            type="radio"
                                            name="type"
                                            value="beverage"
                                            checked={formData.type === 'beverage'}
                                            onChange={(e) => setFormData({ ...formData, type: e.target.value as 'food' | 'beverage' })}
                                            className="h-4 w-4 text-primary focus:ring-primary"
                                        />
                                        <span className="text-sm">Beverage</span>
                                    </label>
                                </div>
                                {errors.type && (
                                    <p className="text-sm text-red-500">{errors.type}</p>
                                )}
                            </div>

                            {/* Availability */}
                            <div className="space-y-2">
                                <Label>Availability</Label>
                                <div className="flex items-center gap-2">
                                    <input
                                        type="checkbox"
                                        id="is_available"
                                        checked={formData.is_available}
                                        onChange={(e) => setFormData({ ...formData, is_available: e.target.checked })}
                                        className="h-4 w-4 text-primary focus:ring-primary rounded"
                                    />
                                    <Label htmlFor="is_available" className="cursor-pointer font-normal">
                                        Item is available for ordering
                                    </Label>
                                </div>
                            </div>

                            {/* Submit Button */}
                            <div className="flex justify-end gap-3 pt-4">
                                <Button
                                    type="button"
                                    variant="outline"
                                    onClick={() => router.visit('/foods')}
                                    disabled={processing}
                                >
                                    Cancel
                                </Button>
                                <Button type="submit" disabled={processing}>
                                    {processing && <Loader2 className="mr-2 h-4 w-4 animate-spin" />}
                                    {processing ? 'Updating...' : 'Update Food Item'}
                                </Button>
                            </div>
                        </form>
                    </CardContent>
                </Card>
            </div>
        </AppLayout>
    );
}
