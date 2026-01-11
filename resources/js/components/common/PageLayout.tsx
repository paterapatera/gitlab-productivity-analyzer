import { ReactNode } from 'react';

interface PageLayoutProps {
    title: string;
    children: ReactNode;
    headerAction?: ReactNode;
}

/**
 * ページの共通レイアウトコンポーネント
 */
export function PageLayout({ title, children, headerAction }: PageLayoutProps) {
    return (
        <div className="container mx-auto px-4 py-8">
            {headerAction ? (
                <div className="mb-6 flex items-center justify-between">
                    <h1 className="text-2xl font-bold">{title}</h1>
                    {headerAction}
                </div>
            ) : (
                <h1 className="mb-6 text-2xl font-bold">{title}</h1>
            )}
            {children}
        </div>
    );
}
