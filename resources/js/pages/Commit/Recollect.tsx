import { FlashMessage } from '@/components/common/FlashMessage';
import { LoadingButton } from '@/components/common/LoadingButton';
import { PageLayout } from '@/components/common/PageLayout';
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from '@/components/ui/table';
import { formatDate } from '@/lib/utils';
import { RecollectPageProps } from '@/types/commit';
import { Head, router } from '@inertiajs/react';
import { RefreshCwIcon } from 'lucide-react';
import { useState } from 'react';

export default function Recollect({
    histories,
    error,
    success,
}: RecollectPageProps) {
    const [processingStates, setProcessingStates] = useState<
        Record<string, boolean>
    >({});

    const handleRecollect = (projectId: number, branchName: string): void => {
        const key = `${projectId}-${branchName}`;
        setProcessingStates((prev) => ({ ...prev, [key]: true }));

        router.post(
            '/commits/recollect',
            {
                project_id: projectId,
                branch_name: branchName,
            },
            {
                onFinish: () => {
                    setProcessingStates((prev) => ({ ...prev, [key]: false }));
                },
            },
        );
    };

    return (
        <>
            <Head title="再収集" />
            <PageLayout title="再収集">
                <FlashMessage error={error} success={success} />

                {histories.length === 0 ? (
                    <div className="py-12 text-center">
                        <p className="text-muted-foreground">
                            収集履歴が存在しません
                        </p>
                    </div>
                ) : (
                    <div className="rounded-md border">
                        <Table>
                            <TableHeader>
                                <TableRow>
                                    <TableHead>プロジェクト名</TableHead>
                                    <TableHead>ブランチ名</TableHead>
                                    <TableHead>前回の最新日時</TableHead>
                                    <TableHead>操作</TableHead>
                                </TableRow>
                            </TableHeader>
                            <TableBody>
                                {histories.map((history) => {
                                    const key = `${history.project_id}-${history.branch_name}`;
                                    const isProcessing =
                                        processingStates[key] ?? false;

                                    return (
                                        <TableRow key={key}>
                                            <TableCell>
                                                {
                                                    history.project_name_with_namespace
                                                }
                                            </TableCell>
                                            <TableCell>
                                                {history.branch_name}
                                            </TableCell>
                                            <TableCell
                                                className={
                                                    history.latest_committed_date
                                                        ? ''
                                                        : 'text-muted-foreground'
                                                }
                                            >
                                                {formatDate(
                                                    history.latest_committed_date,
                                                )}
                                            </TableCell>
                                            <TableCell>
                                                <LoadingButton
                                                    onClick={() =>
                                                        handleRecollect(
                                                            history.project_id,
                                                            history.branch_name,
                                                        )
                                                    }
                                                    loading={isProcessing}
                                                    loadingText="再収集中..."
                                                    variant="default"
                                                    size="sm"
                                                >
                                                    <RefreshCwIcon />
                                                    再収集
                                                </LoadingButton>
                                            </TableCell>
                                        </TableRow>
                                    );
                                })}
                            </TableBody>
                        </Table>
                    </div>
                )}
            </PageLayout>
        </>
    );
}
