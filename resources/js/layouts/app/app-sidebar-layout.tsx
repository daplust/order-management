import { AppContent } from '@/components/app-content';
import { AppShell } from '@/components/app-shell';
import { AppSidebar } from '@/components/app-sidebar';
import { AppSidebarHeader } from '@/components/app-sidebar-header';
import { type BreadcrumbItem } from '@/types';
import { type PropsWithChildren, useEffect, useRef } from 'react';
import { Toaster, toast } from 'sonner';
import { usePage } from '@inertiajs/react';

export default function AppSidebarLayout({
    children,
    breadcrumbs = [],
}: PropsWithChildren<{ breadcrumbs?: BreadcrumbItem[] }>) {
    const { flash } = usePage<{ flash: { success?: string; error?: string } }>().props;
    const prevFlash = useRef<{ success?: string; error?: string }>({});

    useEffect(() => {
        if (flash?.success && flash.success !== prevFlash.current.success) {
            toast.success(flash.success);
            prevFlash.current.success = flash.success;
        }
        if (flash?.error && flash.error !== prevFlash.current.error) {
            toast.error(flash.error);
            prevFlash.current.error = flash.error;
        }
    }, [flash]);

    return (
        <AppShell variant="sidebar">
            <AppSidebar />
            <AppContent variant="sidebar" className="overflow-x-hidden">
                <AppSidebarHeader breadcrumbs={breadcrumbs} />
                {children}
            </AppContent>
            <Toaster 
                position="top-right" 
                richColors 
                closeButton
                expand={false}
            />
        </AppShell>
    );
}
