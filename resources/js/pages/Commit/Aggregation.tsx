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
                                集計データが存在しません
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
                                            <>
                                                <Bar
                                                    key={`${userName}_additions`}
                                                    dataKey={`${userName}_additions`}
                                                    stackId={userName}
                                                    fill="#3b82f6"
                                                    name={`${userName} (追加)`}
                                                />
                                                <Bar
                                                    key={`${userName}_deletions`}
                                                    dataKey={`${userName}_deletions`}
                                                    stackId={userName}
                                                    fill="#ef4444"
                                                    name={`${userName} (削除)`}
                                                />
                                            </>
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
