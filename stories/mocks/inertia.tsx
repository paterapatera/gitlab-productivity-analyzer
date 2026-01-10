import React from 'react';
import { fn } from 'storybook/test';

// グローバルなprocessing状態を管理（ストーリー間で共有）
let globalProcessing = false;
const processingCallbacks: Set<(value: boolean) => void> = new Set();

// Inertia.jsのモック実装
export const useForm = (initialData: Record<string, any> = {}) => {
    const [processing, setProcessing] = React.useState(globalProcessing);

    React.useEffect(() => {
        processingCallbacks.add(setProcessing);
        setProcessing(globalProcessing);
        return () => {
            processingCallbacks.delete(setProcessing);
        };
    }, []);

    const post = (url: string, options?: any) => {
        // ストーリーで処理状態を確認できるように、少し遅延させる
        processingCallbacks.forEach((cb) => cb(true));
        setTimeout(() => {
            processingCallbacks.forEach((cb) => cb(false));
            if (options?.onSuccess) {
                options.onSuccess();
            }
        }, 2000);
        fn()(url, options);
    };

    return {
        data: initialData,
        setData: fn(),
        post,
        processing,
        errors: {},
        reset: fn(),
        clearErrors: fn(),
        setError: fn(),
        submit: fn(),
        get: fn(),
        put: fn(),
        patch: fn(),
        delete: fn(),
        cancel: fn(),
    };
};

// ローディング状態を設定する関数（ストーリーから使用）
export const setProcessing = (value: boolean) => {
    globalProcessing = value;
    processingCallbacks.forEach((cb) => cb(value));
};

export const Head = ({ children, title }: { children?: React.ReactNode; title?: string }) => {
    React.useEffect(() => {
        if (title) {
            document.title = title;
        }
    }, [title]);

    return <>{children}</>;
};

export const router = {
    get: fn(),
    post: fn(),
    put: fn(),
    patch: fn(),
    delete: fn(),
    reload: fn(),
    visit: fn(),
    replace: fn(),
    remember: fn(),
    restore: fn(),
};
