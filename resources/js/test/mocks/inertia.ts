import { vi } from 'vitest';

/**
 * Inertia.js の useForm の戻り値型
 */
export type MockUseFormReturn = {
    post: ReturnType<typeof vi.fn>;
    processing: boolean;
    errors: Record<string, string>;
    clearErrors: ReturnType<typeof vi.fn>;
    data: Record<string, unknown>;
    setData: ReturnType<typeof vi.fn>;
    reset: ReturnType<typeof vi.fn>;
    setError: ReturnType<typeof vi.fn>;
    submit: ReturnType<typeof vi.fn>;
    get: ReturnType<typeof vi.fn>;
    put: ReturnType<typeof vi.fn>;
    patch: ReturnType<typeof vi.fn>;
    delete: ReturnType<typeof vi.fn>;
    cancel: ReturnType<typeof vi.fn>;
    isDirty: boolean;
    hasErrors: boolean;
    progress: undefined;
    wasSuccessful: boolean;
    recentlySuccessful: boolean;
    transform: ReturnType<typeof vi.fn>;
    resetOnError: ReturnType<typeof vi.fn>;
    resetOnSuccess: ReturnType<typeof vi.fn>;
    setDefaultsOnSuccess: ReturnType<typeof vi.fn>;
};

/**
 * useForm のモックオブジェクトを作成するヘルパー関数
 */
export function createMockUseForm(
    overrides: Partial<MockUseFormReturn> = {},
): MockUseFormReturn {
    return {
        post: vi.fn(),
        processing: false,
        errors: {},
        clearErrors: vi.fn(),
        data: {},
        setData: vi.fn(),
        reset: vi.fn(),
        setError: vi.fn(),
        submit: vi.fn(),
        get: vi.fn(),
        put: vi.fn(),
        patch: vi.fn(),
        delete: vi.fn(),
        cancel: vi.fn(),
        isDirty: false,
        hasErrors: false,
        progress: undefined,
        wasSuccessful: false,
        recentlySuccessful: false,
        transform: vi.fn(),
        resetOnError: vi.fn(),
        resetOnSuccess: vi.fn(),
        setDefaultsOnSuccess: vi.fn(),
        ...overrides,
    };
}
