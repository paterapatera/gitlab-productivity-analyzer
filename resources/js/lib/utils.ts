import { type ClassValue, clsx } from 'clsx';
import { twMerge } from 'tailwind-merge';

export function cn(...inputs: ClassValue[]) {
    return twMerge(clsx(inputs));
}

/**
 * ISO 8601形式の日付文字列をYYYY-MM-DD HH:mm:ss形式にフォーマット
 *
 * @param  isoDateString  ISO 8601形式の日付文字列
 * @return フォーマットされた日付文字列、または'未収集'（無効な日付の場合）
 */
export function formatDate(isoDateString: string): string {
    if (!isoDateString) {
        return '未収集';
    }

    try {
        const date = new Date(isoDateString);
        if (isNaN(date.getTime())) {
            return '未収集';
        }

        const year = date.getFullYear();
        const month = String(date.getMonth() + 1).padStart(2, '0');
        const day = String(date.getDate()).padStart(2, '0');
        const hours = String(date.getHours()).padStart(2, '0');
        const minutes = String(date.getMinutes()).padStart(2, '0');
        const seconds = String(date.getSeconds()).padStart(2, '0');

        return `${year}-${month}-${day} ${hours}:${minutes}:${seconds}`;
    } catch {
        return '未収集';
    }
}
