import { FlashMessage } from '@/components/common/FlashMessage';
import { PageLayout } from '@/components/common/PageLayout';
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
import { aggregation } from '@/routes/commits';
import { AggregationPageProps } from '@/types/commit';
import { Head, router } from '@inertiajs/react';
import { useMemo } from 'react';
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

export default function Aggregation({
    projects,
    branches,
    years,
    aggregations,
    chartData,
    tableData,
    userNames,
    selectedProjectId,
    selectedBranchName,
    selectedYear,
    selectedBranch,
    error,
    success,
}: AggregationPageProps) {
    // セレクトボックスの変更時にページ遷移
    const handleFilterChange = (
        projectId?: string | null,
        branchName?: string | null,
        year?: string | null,
    ) => {
        const query: Record<string, string> = {};
        if (projectId) query.project_id = projectId;
        if (branchName) query.branch_name = branchName;
        if (year) query.year = year;

        router.get(aggregation.url({ query }), {
            preserveState: true,
            preserveScroll: true,
        });
    };

    const months = [1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12];

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
            <Head title="集計" />
            <PageLayout title="集計">
                <FlashMessage error={error} success={success} />

                <div className="space-y-6">
                    {/* フィルター */}
                    <div className="rounded-md border p-6">
                        <div className="grid gap-4 md:grid-cols-3">
                            <div className="space-y-2">
                                <label className="text-sm font-medium">
                                    プロジェクト・ブランチ
                                </label>
                                <Select
                                    value={
                                        selectedBranch
                                            ? `${selectedBranch.project_id}:${selectedBranch.branch_name}`
                                            : undefined
                                    }
                                    onValueChange={(value) => {
                                        const [projectId, branchName] =
                                            value.split(':');
                                        handleFilterChange(
                                            projectId,
                                            branchName,
                                            selectedYear?.toString() ||
                                                undefined,
                                        );
                                    }}
                                >
                                    <SelectTrigger className="w-full">
                                        <SelectValue placeholder="プロジェクト・ブランチを選択" />
                                    </SelectTrigger>
                                    <SelectContent>
                                        {branches.map((branch) => {
                                            const project = projects.find(
                                                (p) =>
                                                    p.id === branch.project_id,
                                            );
                                            const label = project
                                                ? `${project.name_with_namespace}:${branch.branch_name}`
                                                : `${branch.project_id}:${branch.branch_name}`;
                                            return (
                                                <SelectItem
                                                    key={`${branch.project_id}:${branch.branch_name}`}
                                                    value={`${branch.project_id}:${branch.branch_name}`}
                                                >
                                                    {label}
                                                </SelectItem>
                                            );
                                        })}
                                    </SelectContent>
                                </Select>
                            </div>

                            <div className="space-y-2">
                                <label className="text-sm font-medium">
                                    年
                                </label>
                                <Select
                                    value={
                                        selectedYear?.toString() || undefined
                                    }
                                    onValueChange={(value) => {
                                        handleFilterChange(
                                            selectedProjectId?.toString() ||
                                                undefined,
                                            selectedBranchName || undefined,
                                            value,
                                        );
                                    }}
                                    disabled={
                                        !selectedProjectId ||
                                        !selectedBranchName
                                    }
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
                        </div>
                    </div>

                    {/* データ表示 */}
                    {aggregations.length === 0 ? (
                        <div className="py-12 text-center">
                            <p className="text-muted-foreground">
                                {selectedProjectId &&
                                selectedBranchName &&
                                selectedYear
                                    ? '集計データが存在しません'
                                    : 'プロジェクト・ブランチと年を選択してください'}
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
