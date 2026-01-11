import { FlashMessage } from '@/components/common/FlashMessage';
import { PageLayout } from '@/components/common/PageLayout';
import { Checkbox } from '@/components/ui/checkbox';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from '@/components/ui/table';
import { userProductivity } from '@/routes/commits';
import { UserProductivityPageProps } from '@/types/user';
import { Head, router } from '@inertiajs/react';
import { useEffect, useMemo, useState } from 'react';
import {
    Bar,
    BarChart,
    CartesianGrid,
    Legend,
    ResponsiveContainer,
    Tooltip,
    XAxis,
    YAxis,
} from 'recharts';

export default function UserProductivity({
    users,
    years,
    chartData,
    tableData,
    userNames,
    selectedYear,
    selectedUsers,
    error,
    success,
}: UserProductivityPageProps) {
    // 選択されたユーザーの状態管理
    // selectedUsersが変更されたときにcheckedUsersを更新するため、useStateの初期値として使用
    const [checkedUsers, setCheckedUsers] = useState<string[]>(
        selectedUsers || [],
    );

    // selectedUsersが変更されたときにcheckedUsersを更新
    // 注意: useEffect内でのsetStateはReactのベストプラクティスに反するが、
    // この場合はpropsの変更をローカルステートに反映する必要があるため使用
    // 代替案として、keyプロップを使用してコンポーネントを再マウントすることも可能だが、
    // その場合はユーザーのチェック状態が失われるため、この方法を採用
    useEffect(() => {
        if (selectedUsers) {
            setCheckedUsers(selectedUsers);
        }
    }, [selectedUsers]);

    // フィルター変更時にページ遷移
    const handleFilterChange = (year?: string | null, users?: string[]) => {
        const query: Record<string, string | string[]> = {};
        if (year) query.year = year;
        if (users && users.length > 0) {
            query.users = users;
        }

        router.get(userProductivity.url({ query }), {
            preserveState: true,
            preserveScroll: true,
        });
    };

    // 年フィルター変更
    const handleYearChange = (year: string) => {
        handleFilterChange(year, checkedUsers);
    };

    // ユーザーチェックボックス変更
    const handleUserToggle = (userEmail: string, checked: boolean) => {
        const newCheckedUsers = checked
            ? [...checkedUsers, userEmail]
            : checkedUsers.filter((email) => email !== userEmail);

        setCheckedUsers(newCheckedUsers);
        handleFilterChange(
            selectedYear?.toString() || undefined,
            newCheckedUsers,
        );
    };

    // 全ユーザー選択/解除
    const handleSelectAll = (checked: boolean) => {
        const allUserEmails = users.map((user) => user.author_email);
        const newCheckedUsers = checked ? allUserEmails : [];
        setCheckedUsers(newCheckedUsers);
        handleFilterChange(
            selectedYear?.toString() || undefined,
            newCheckedUsers,
        );
    };

    const months = [1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12];
    const hasData = tableData.length > 0;
    const allUsersSelected =
        checkedUsers.length === users.length && users.length > 0;

    // ユーザーごとに色を生成する関数
    const getUserColor = (userName: string, index: number): string => {
        // 色相を均等に分散（HSL色空間を使用）
        const hue = (index * 137.508) % 360; // 黄金角を使用して均等に分散
        const saturation = 60 + (index % 3) * 10; // 60-80%の範囲で変化
        const lightness = 45 + (index % 2) * 5; // 45-50%の範囲で変化

        // HSLをRGBに変換
        const h = hue / 360;
        const s = saturation / 100;
        const l = lightness / 100;

        const c = (1 - Math.abs(2 * l - 1)) * s;
        const x = c * (1 - Math.abs(((h * 6) % 2) - 1));
        const m = l - c / 2;

        let r = 0;
        let g = 0;
        let b = 0;

        if (h * 6 < 1) {
            r = c;
            g = x;
            b = 0;
        } else if (h * 6 < 2) {
            r = x;
            g = c;
            b = 0;
        } else if (h * 6 < 3) {
            r = 0;
            g = c;
            b = x;
        } else if (h * 6 < 4) {
            r = 0;
            g = x;
            b = c;
        } else if (h * 6 < 5) {
            r = x;
            g = 0;
            b = c;
        } else {
            r = c;
            g = 0;
            b = x;
        }

        r = Math.round((r + m) * 255);
        g = Math.round((g + m) * 255);
        b = Math.round((b + m) * 255);

        return `#${r.toString(16).padStart(2, '0')}${g.toString(16).padStart(2, '0')}${b.toString(16).padStart(2, '0')}`;
    };

    // ユーザー名と色のマッピングを生成
    const userColorMap = useMemo(() => {
        const map = new Map<string, string>();
        userNames.forEach((userName, index) => {
            map.set(userName, getUserColor(userName, index));
        });
        return map;
    }, [userNames]);

    return (
        <>
            <Head title="ユーザー生産性" />
            <PageLayout title="ユーザー生産性">
                <FlashMessage error={error} success={success} />

                <div className="space-y-6">
                    {/* フィルター */}
                    <div className="rounded-md border p-6">
                        <div className="grid gap-4 md:grid-cols-2">
                            <div className="space-y-2">
                                <label className="text-sm font-medium">
                                    年
                                </label>
                                <Select
                                    value={
                                        selectedYear?.toString() || undefined
                                    }
                                    onValueChange={handleYearChange}
                                >
                                    <SelectTrigger className="w-full">
                                        <SelectValue placeholder="年を選択" />
                                    </SelectTrigger>
                                    <SelectContent>
                                        {years.map((year) => (
                                            <SelectItem
                                                key={year}
                                                value={year.toString()}
                                            >
                                                {year}年
                                            </SelectItem>
                                        ))}
                                    </SelectContent>
                                </Select>
                            </div>

                            <div className="space-y-2">
                                <label className="text-sm font-medium">
                                    ユーザー
                                </label>
                                <div className="max-h-48 space-y-2 overflow-y-auto rounded-md border p-3">
                                    <div className="flex items-center space-x-2">
                                        <Checkbox
                                            id="select-all"
                                            checked={allUsersSelected}
                                            onCheckedChange={handleSelectAll}
                                        />
                                        <label
                                            htmlFor="select-all"
                                            className="text-sm leading-none font-medium peer-disabled:cursor-not-allowed peer-disabled:opacity-70"
                                        >
                                            全選択
                                        </label>
                                    </div>
                                    <div className="border-t pt-2" />
                                    {users.map((user) => {
                                        const isChecked = checkedUsers.includes(
                                            user.author_email,
                                        );
                                        return (
                                            <div
                                                key={user.author_email}
                                                className="flex items-center space-x-2"
                                            >
                                                <Checkbox
                                                    id={user.author_email}
                                                    checked={isChecked}
                                                    onCheckedChange={(
                                                        checked,
                                                    ) =>
                                                        handleUserToggle(
                                                            user.author_email,
                                                            checked === true,
                                                        )
                                                    }
                                                />
                                                <label
                                                    htmlFor={user.author_email}
                                                    className="text-sm leading-none peer-disabled:cursor-not-allowed peer-disabled:opacity-70"
                                                >
                                                    {user.author_name ||
                                                        'Unknown'}
                                                </label>
                                            </div>
                                        );
                                    })}
                                </div>
                            </div>
                        </div>
                    </div>

                    {/* データ表示 */}
                    {!hasData ? (
                        <div className="py-12 text-center">
                            <p className="text-muted-foreground">
                                集計データが存在しません。年とユーザーを選択してください。
                            </p>
                        </div>
                    ) : (
                        <>
                            {/* 棒グラフ */}
                            <div className="rounded-md border p-6">
                                <h2 className="mb-4 text-lg font-semibold">
                                    月別行数推移
                                </h2>
                                <ResponsiveContainer width="100%" height={400}>
                                    <BarChart data={chartData}>
                                        <CartesianGrid strokeDasharray="3 3" />
                                        <XAxis dataKey="month" />
                                        <YAxis />
                                        <Tooltip />
                                        <Legend />
                                        {userNames.map((userName) => (
                                            <Bar
                                                key={userName}
                                                dataKey={`${userName}_total`}
                                                fill={
                                                    userColorMap.get(
                                                        userName,
                                                    ) || '#3b82f6'
                                                }
                                                name={userName}
                                            />
                                        ))}
                                    </BarChart>
                                </ResponsiveContainer>
                            </div>

                            {/* 表 */}
                            <div className="rounded-md border">
                                <Table>
                                    <TableHeader>
                                        <TableRow>
                                            <TableHead>ユーザー</TableHead>
                                            {months.map((month) => (
                                                <TableHead key={month}>
                                                    {month}月
                                                </TableHead>
                                            ))}
                                        </TableRow>
                                    </TableHeader>
                                    <TableBody>
                                        {tableData.map((row) => (
                                            <TableRow key={row.userKey}>
                                                <TableCell>
                                                    {row.userName}
                                                </TableCell>
                                                {months.map((month) => (
                                                    <TableCell key={month}>
                                                        {row.months[month] || 0}
                                                    </TableCell>
                                                ))}
                                            </TableRow>
                                        ))}
                                    </TableBody>
                                </Table>
                            </div>
                        </>
                    )}
                </div>
            </PageLayout>
        </>
    );
}
