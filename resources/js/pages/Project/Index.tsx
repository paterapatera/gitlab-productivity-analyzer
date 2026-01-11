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
import { ProjectPageProps } from '@/types/project';
import { Head, useForm } from '@inertiajs/react';
import { RefreshCwIcon } from 'lucide-react';

export default function Index({ projects, error, success }: ProjectPageProps) {
    const { post, processing } = useForm({});

    const handleSync = () => {
        post('/projects/sync');
    };

    return (
        <>
            <Head title="プロジェクト一覧" />
            <PageLayout
                title="プロジェクト一覧"
                headerAction={
                    <LoadingButton
                        onClick={handleSync}
                        loading={processing}
                        loadingText="同期中..."
                        variant="default"
                    >
                        <RefreshCwIcon />
                        同期
                    </LoadingButton>
                }
            >
                <FlashMessage error={error} success={success} />

                {projects.length === 0 ? (
                    <div className="py-12 text-center">
                        <p className="text-muted-foreground">
                            プロジェクトが存在しません
                        </p>
                    </div>
                ) : (
                    <div className="rounded-md border">
                        <Table>
                            <TableHeader>
                                <TableRow>
                                    <TableHead>ID</TableHead>
                                    <TableHead>プロジェクト名</TableHead>
                                    <TableHead>説明</TableHead>
                                    <TableHead>デフォルトブランチ</TableHead>
                                </TableRow>
                            </TableHeader>
                            <TableBody>
                                {projects.map((project) => (
                                    <TableRow key={project.id}>
                                        <TableCell className="font-medium">
                                            {project.id}
                                        </TableCell>
                                        <TableCell>
                                            {project.name_with_namespace}
                                        </TableCell>
                                        <TableCell className="text-muted-foreground">
                                            {project.description || '-'}
                                        </TableCell>
                                        <TableCell className="text-muted-foreground">
                                            {project.default_branch || '-'}
                                        </TableCell>
                                    </TableRow>
                                ))}
                            </TableBody>
                        </Table>
                    </div>
                )}
            </PageLayout>
        </>
    );
}
