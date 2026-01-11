import { Button, buttonVariants } from '@/components/ui/button';
import { type VariantProps } from 'class-variance-authority';
import { RefreshCwIcon } from 'lucide-react';
import { ButtonHTMLAttributes, ReactNode } from 'react';

interface LoadingButtonProps
    extends
        Omit<ButtonHTMLAttributes<HTMLButtonElement>, 'children'>,
        VariantProps<typeof buttonVariants> {
    loading: boolean;
    loadingText: string;
    children: ReactNode;
}

/**
 * ローディング状態を表示するボタンコンポーネント
 */
export function LoadingButton({
    loading,
    loadingText,
    children,
    disabled,
    ...props
}: LoadingButtonProps) {
    return (
        <Button disabled={disabled || loading} {...props}>
            {loading ? (
                <>
                    <RefreshCwIcon className="animate-spin" />
                    {loadingText}
                </>
            ) : (
                children
            )}
        </Button>
    );
}
