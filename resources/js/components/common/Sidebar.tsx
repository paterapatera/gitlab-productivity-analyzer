import {
    Sidebar as ShadcnSidebar,
    SidebarContent,
    SidebarMenu,
    SidebarMenuButton,
    SidebarMenuItem,
} from '@/components/ui/sidebar';
import { menuItems } from '@/lib/menu-items';
import { Link, usePage } from '@inertiajs/react';

/**
 * 現在の URL パス名を取得する
 * @param url Inertia.js の usePage() で取得した URL
 * @returns パス名（例: '/projects', '/commits/collect'）
 */
function getPathname(url: string): string {
    try {
        return new URL(url, window.location.origin).pathname;
    } catch {
        // URL の解析に失敗した場合は、url をそのまま返す
        return url.split('?')[0];
    }
}

/**
 * メニュー項目がアクティブかどうかを判定する
 * @param currentPathname 現在のページのパス名
 * @param menuItemPath メニュー項目のパス名
 * @returns アクティブかどうか
 */
function isMenuItemActive(
    currentPathname: string,
    menuItemPath: string,
): boolean {
    // 完全一致
    if (currentPathname === menuItemPath) {
        return true;
    }
    // 部分一致（現在のページがメニュー項目のパスで始まる場合）
    if (currentPathname.startsWith(menuItemPath + '/')) {
        return true;
    }
    return false;
}

/**
 * サイドバーメニューコンポーネント
 * 5つの主要画面へのナビゲーションを提供
 */
export function Sidebar() {
    const { url } = usePage();
    const currentPathname = getPathname(url);

    return (
        <ShadcnSidebar>
            <SidebarContent>
                <SidebarMenu>
                    {menuItems.map((item) => {
                        const isActive = isMenuItemActive(
                            currentPathname,
                            item.path,
                        );
                        return (
                            <SidebarMenuItem key={item.path}>
                                <SidebarMenuButton asChild isActive={isActive}>
                                    <Link href={item.route().url}>
                                        {item.label}
                                    </Link>
                                </SidebarMenuButton>
                            </SidebarMenuItem>
                        );
                    })}
                </SidebarMenu>
            </SidebarContent>
        </ShadcnSidebar>
    );
}
