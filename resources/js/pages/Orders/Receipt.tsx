import { Head, router } from '@inertiajs/react';
import AppLayout from '@/layouts/app-layout';
import { type BreadcrumbItem } from '@/types';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Printer, Download, ArrowLeft } from 'lucide-react';

interface ReceiptItem {
    name: string;
    type: string;
    quantity: number;
    unit_price: number | string;
    subtotal: number | string;
    special_instructions?: string;
}

interface Receipt {
    receipt_number: string;
    order_id: number;
    date: string;
    table: {
        number: string;
        capacity: number;
    };
    items: ReceiptItem[];
    summary: {
        subtotal: number | string;
        tax: number | string;
        service_charge: number | string;
        grand_total: number | string;
    };
    order_info: {
        opened_at: string;
        closed_at: string;
        status: string;
        notes?: string;
    };
}

interface ReceiptProps {
    receipt: Receipt;
}

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Orders', href: '/orders' },
    { title: 'Receipt', href: '#' },
];

export default function OrderReceipt({ receipt }: ReceiptProps) {
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

    const handlePrint = () => {
        window.print();
    };

    const handleDownloadPDF = () => {
        window.open(`/orders/${receipt.order_id}/receipt/pdf`, '_blank');
    };

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title={`Receipt - ${receipt.receipt_number}`} />

            <div className="space-y-6 p-6">
                {/* Header */}
                <div className="flex items-center justify-between no-print">
                    <div className="flex items-center gap-4">
                        <Button
                            variant="outline"
                            size="sm"
                            onClick={() => router.visit(`/orders/${receipt.order_id}`)}
                        >
                            <ArrowLeft className="mr-2 h-4 w-4" />
                            Back to Order
                        </Button>
                        <div>
                            <h1 className="text-3xl font-bold tracking-tight">Receipt</h1>
                            <p className="text-muted-foreground">{receipt.receipt_number}</p>
                        </div>
                    </div>
                    <div className="flex gap-2">
                        <Button onClick={handlePrint} variant="outline">
                            <Printer className="mr-2 h-4 w-4" />
                            Print
                        </Button>
                        <Button onClick={handleDownloadPDF}>
                            <Download className="mr-2 h-4 w-4" />
                            Download PDF
                        </Button>
                    </div>
                </div>

                {/* Receipt */}
                <Card className="mx-auto max-w-2xl print-area">
                    <CardHeader className="border-b pb-6">
                        <div className="text-center space-y-2">
                            <CardTitle className="text-2xl">Restaurant Order Management</CardTitle>
                            <p className="text-sm text-muted-foreground">
                                123 Restaurant Street, City, Indonesia 12345
                            </p>
                            <p className="text-sm text-muted-foreground">
                                Tel: (123) 456-7890
                            </p>
                        </div>
                    </CardHeader>
                    <CardContent className="pt-6 space-y-6">
                        {/* Receipt Info */}
                        <div className="space-y-2 border-b pb-4">
                            <div className="flex justify-between text-sm">
                                <span className="text-muted-foreground">Receipt Number:</span>
                                <span className="font-medium">{receipt.receipt_number}</span>
                            </div>
                            <div className="flex justify-between text-sm">
                                <span className="text-muted-foreground">Order ID:</span>
                                <span className="font-medium">#{receipt.order_id.toString().padStart(4, '0')}</span>
                            </div>
                            <div className="flex justify-between text-sm">
                                <span className="text-muted-foreground">Date:</span>
                                <span className="font-medium">{formatDate(receipt.date)}</span>
                            </div>
                            <div className="flex justify-between text-sm">
                                <span className="text-muted-foreground">Table:</span>
                                <span className="font-medium">{receipt.table.number}</span>
                            </div>
                        </div>

                        {/* Order Items */}
                        <div className="space-y-3">
                            <h3 className="font-semibold">Order Items</h3>
                            <div className="space-y-2">
                                {receipt.items.map((item, index) => (
                                    <div key={index} className="space-y-1">
                                        <div className="flex justify-between text-sm">
                                            <div className="flex-1">
                                                <div className="font-medium">{item.name}</div>
                                                {item.special_instructions && (
                                                    <div className="text-xs text-muted-foreground italic">
                                                        Note: {item.special_instructions}
                                                    </div>
                                                )}
                                            </div>
                                            <div className="text-right">
                                                <div className="font-medium">{formatCurrency(item.subtotal)}</div>
                                                <div className="text-xs text-muted-foreground">
                                                    {item.quantity} × {formatCurrency(item.unit_price)}
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                ))}
                            </div>
                        </div>

                        {/* Summary */}
                        <div className="space-y-2 border-t pt-4">
                            <div className="flex justify-between text-sm">
                                <span className="text-muted-foreground">Subtotal:</span>
                                <span className="font-medium">{formatCurrency(receipt.summary.subtotal)}</span>
                            </div>
                            <div className="flex justify-between text-sm">
                                <span className="text-muted-foreground">Tax (10%):</span>
                                <span className="font-medium">{formatCurrency(receipt.summary.tax)}</span>
                            </div>
                            <div className="flex justify-between text-sm">
                                <span className="text-muted-foreground">Service Charge (5%):</span>
                                <span className="font-medium">{formatCurrency(receipt.summary.service_charge)}</span>
                            </div>
                            <div className="flex justify-between border-t pt-2 text-lg font-bold">
                                <span>Total:</span>
                                <span>{formatCurrency(receipt.summary.grand_total)}</span>
                            </div>
                        </div>

                        {/* Order Notes */}
                        {receipt.order_info.notes && (
                            <div className="rounded-lg bg-muted p-4">
                                <h4 className="text-sm font-medium mb-1">Order Notes:</h4>
                                <p className="text-sm text-muted-foreground">{receipt.order_info.notes}</p>
                            </div>
                        )}

                        {/* Footer */}
                        <div className="border-t pt-6 text-center space-y-2">
                            <p className="text-sm font-medium">Thank you for your visit!</p>
                            <p className="text-xs text-muted-foreground">
                                Happy Tummy Happy Us
                            </p>
                        </div>
                    </CardContent>
                </Card>
            </div>

            <style>{`
                @media print {
                    /* Hide everything except print area */
                    .no-print {
                        display: none !important;
                    }
                    
                    /* Show only the receipt */
                    .print-area {
                        position: absolute;
                        left: 0;
                        top: 0;
                        width: 100%;
                        max-width: 100% !important;
                        margin: 0 !important;
                        padding: 20px !important;
                        box-shadow: none !important;
                        border: none !important;
                    }
                    
                    /* Hide buttons in print */
                    button {
                        display: none !important;
                    }
                    
                    /* Remove page margins */
                    @page {
                        margin: 0.5cm;
                    }
                    
                    /* Ensure text is black for printing */
                    body {
                        color: black !important;
                        background: white !important;
                    }
                }
            `}</style>
        </AppLayout>
    );
}
