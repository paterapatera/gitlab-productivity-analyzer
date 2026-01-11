import React from 'react';
import { fn } from 'storybook/test';

/**
 * グローバルな processing 状態を管理（ストーリー間で共有）
 */
let globalProcessing = false;
const processingCallbacks: Set<(value: boolean) => void> = new Set();

/**
 * Inertia.js の useForm フックのモック実装
 */
export const useForm = (initialData: Record<string, unknown> = {}) => {
  const [processing, setProcessing] = React.useState(globalProcessing);

  React.useEffect(() => {
    processingCallbacks.add(setProcessing);
    setProcessing(globalProcessing);
    return () => {
      processingCallbacks.delete(setProcessing);
    };
  }, []);

  const post = (url: string, options?: { onSuccess?: () => void }) => {
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

/**
 * ローディング状態を設定する関数（ストーリーから使用）
 */
export const setProcessing = (value: boolean) => {
  globalProcessing = value;
  processingCallbacks.forEach((cb) => cb(value));
};

/**
 * Inertia.js の Head コンポーネントのモック実装
 */
export const Head = ({
  children,
  title,
}: {
  children?: React.ReactNode;
  title?: string;
}) => {
  React.useEffect(() => {
    if (title) {
      document.title = title;
    }
  }, [title]);

  return <>{children}</>;
};

/**
 * Inertia.js の Form コンポーネントのモック実装
 */
export const Form = ({
  action,
  method,
  children,
  className,
  ...props
}: {
  action: string;
  method?: string;
  children: (props: {
    errors: Record<string, string>;
    processing: boolean;
    clearErrors: (key?: string) => void;
    hasErrors?: boolean;
    wasSuccessful?: boolean;
    recentlySuccessful?: boolean;
    resetAndClearErrors?: () => void;
    defaults?: Record<string, unknown>;
  }) => React.ReactNode;
  className?: string;
  [key: string]: unknown;
}) => {
  // globalProcessing を直接参照して、確実に最新の状態を取得
  const [formProcessing, setFormProcessing] =
    React.useState(globalProcessing);
  const { errors, clearErrors } = useForm({});

  React.useEffect(() => {
    const updateProcessing = () => {
      setFormProcessing(globalProcessing);
    };
    processingCallbacks.add(updateProcessing);
    updateProcessing();
    return () => {
      processingCallbacks.delete(updateProcessing);
    };
  }, []);

  return (
    <form
      action={action}
      method={method || 'post'}
      className={className}
      onSubmit={(e) => {
        e.preventDefault();
      }}
      {...props}
    >
      {children({
        errors,
        processing: formProcessing,
        clearErrors,
        hasErrors: Object.keys(errors).length > 0,
        wasSuccessful: false,
        recentlySuccessful: false,
        resetAndClearErrors: () => {},
        defaults: {},
      })}
    </form>
  );
};

/**
 * Inertia.js の router オブジェクトのモック実装
 */
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
