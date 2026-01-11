import { Alert, AlertDescription, AlertTitle } from '@/components/ui/alert';
import { AlertCircleIcon, CheckCircle2Icon } from 'lucide-react';

interface FlashMessageProps {
    error?: string;
    success?: string;
}

/**
 * エラーまたは成功メッセージを表示するコンポーネント
 */
export function FlashMessage({ error, success }: FlashMessageProps) {
    if (error) {
        return (
            <Alert variant="destructive" className="mb-4">
                <AlertCircleIcon />
                <AlertTitle>エラー</AlertTitle>
                <AlertDescription>{error}</AlertDescription>
            </Alert>
        );
    }

    if (success) {
        return (
            <Alert className="mb-4">
                <CheckCircle2Icon />
                <AlertTitle>成功</AlertTitle>
                <AlertDescription>{success}</AlertDescription>
            </Alert>
        );
    }

    return null;
}
