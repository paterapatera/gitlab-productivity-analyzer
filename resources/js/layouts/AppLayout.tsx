import { Sidebar } from '@/components/common/Sidebar';
import {
    SidebarInset,
    SidebarProvider,
    SidebarTrigger,
} from '@/components/ui/sidebar';
import { usePage } from '@inertiajs/react';
import { ReactNode } from 'react';

interface AppLayoutProps {
    children: ReactNode;
}

/**
 * アプリケーション全体のレイアウトコンポーネント
 * サイドバーとメインコンテンツエリアを配置
 */
export function AppLayout({ children }: AppLayoutProps) {
    const { props } = usePage<{ sidebarOpen?: boolean }>();
    const sidebarOpen = props.sidebarOpen ?? true;

    return (
        <SidebarProvider defaultOpen={sidebarOpen}>
            <Sidebar />
            <SidebarInset>
                <header className="flex h-16 shrink-0 items-center gap-2 border-b px-4">
                    <SidebarTrigger />
                </header>
                <div className="flex flex-1 flex-col gap-4 p-4">{children}</div>
            </SidebarInset>
        </SidebarProvider>
    );
}
