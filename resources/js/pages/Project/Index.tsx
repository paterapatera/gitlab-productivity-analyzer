import { Head, useForm } from '@inertiajs/react';
import { RefreshCwIcon, AlertCircleIcon, CheckCircle2Icon } from 'lucide-react';
import { ProjectPageProps } from '@/types/project';
import { Button } from '@/components/ui/button';
import { Alert, AlertDescription, AlertTitle } from '@/components/ui/alert';
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from '@/components/ui/table';

export default function Index({ projects, error, success }: ProjectPageProps) {
    const { post, processing } = useForm({});

    const handleSync = () => {
        post('/projects/sync');
    };

    return (
        <>
            <Head title="プロジェクト一覧" />
            <div className="container mx-auto px-4 py-8">
                <div className="flex justify-between items-center mb-6">
                    <h1 className="text-2xl font-bold">プロジェクト一覧</h1>
                    <Button
                        onClick={handleSync}
                        disabled={processing}
                        variant="default"
                    >
                        {processing ? (
                            <>
                                <RefreshCwIcon className="animate-spin" />
                                同期中...
                            </>
                        ) : (
                            <>
                                <RefreshCwIcon />
                                同期
                            </>
                        )}
                    </Button>
                </div>

                {error && (
                    <Alert variant="destructive" className="mb-4">
                        <AlertCircleIcon />
                        <AlertTitle>エラー</AlertTitle>
                        <AlertDescription>{error}</AlertDescription>
                    </Alert>
                )}

                {success && (
                    <Alert className="mb-4">
                        <CheckCircle2Icon />
                        <AlertTitle>成功</AlertTitle>
                        <AlertDescription>{success}</AlertDescription>
                    </Alert>
                )}

                {projects.length === 0 ? (
                    <div className="text-center py-12">
                        <p className="text-muted-foreground">プロジェクトが存在しません</p>
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
                                        <TableCell>{project.name_with_namespace}</TableCell>
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
            </div>
        </>
    );
}
