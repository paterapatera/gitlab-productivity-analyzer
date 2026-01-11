import { FlashMessage } from '@/components/common/FlashMessage';
import { LoadingButton } from '@/components/common/LoadingButton';
import { PageLayout } from '@/components/common/PageLayout';
import { Input } from '@/components/ui/input';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import { CommitPageProps } from '@/types/commit';
import { Form, Head } from '@inertiajs/react';
import { RefreshCwIcon } from 'lucide-react';
import { useState } from 'react';

export default function Index({ projects, error, success }: CommitPageProps) {
    const [projectId, setProjectId] = useState<string>('');
    const [branchName, setBranchName] = useState<string>('');
    const [sinceDate, setSinceDate] = useState<string>('');

    return (
        <>
            <Head title="コミット収集" />
            <PageLayout title="コミット収集">
                <FlashMessage error={error} success={success} />

                <div className="rounded-md border p-6">
                    <Form
                        action="/commits/collect"
                        method="post"
                        className="space-y-4"
                    >
                        {({ errors, processing, clearErrors }) => (
                            <>
                                <div className="space-y-2">
                                    <label
                                        htmlFor="project_id"
                                        className="text-sm font-medium"
                                    >
                                        プロジェクト
                                    </label>
                                    <input
                                        type="hidden"
                                        name="project_id"
                                        value={projectId}
                                    />
                                    <Select
                                        value={projectId}
                                        onValueChange={(value) => {
                                            setProjectId(value);
                                            clearErrors('project_id');
                                        }}
                                        required
                                    >
                                        <SelectTrigger
                                            id="project_id"
                                            className="w-full"
                                            aria-invalid={!!errors.project_id}
                                        >
                                            <SelectValue placeholder="プロジェクトを選択してください" />
                                        </SelectTrigger>
                                        <SelectContent>
                                            {projects.map((project) => (
                                                <SelectItem
                                                    key={project.id}
                                                    value={project.id.toString()}
                                                >
                                                    {
                                                        project.name_with_namespace
                                                    }
                                                </SelectItem>
                                            ))}
                                        </SelectContent>
                                    </Select>
                                    {errors.project_id && (
                                        <p className="text-sm text-destructive">
                                            {errors.project_id}
                                        </p>
                                    )}
                                </div>

                                <div className="space-y-2">
                                    <label
                                        htmlFor="branch_name"
                                        className="text-sm font-medium"
                                    >
                                        ブランチ名
                                    </label>
                                    <Input
                                        id="branch_name"
                                        name="branch_name"
                                        type="text"
                                        value={branchName}
                                        onChange={(e) => {
                                            setBranchName(e.target.value);
                                            clearErrors('branch_name');
                                        }}
                                        placeholder="例: main"
                                        required
                                        aria-invalid={!!errors.branch_name}
                                    />
                                    {errors.branch_name && (
                                        <p className="text-sm text-destructive">
                                            {errors.branch_name}
                                        </p>
                                    )}
                                </div>

                                <div className="space-y-2">
                                    <label
                                        htmlFor="since_date"
                                        className="text-sm font-medium"
                                    >
                                        開始日 (オプション)
                                    </label>
                                    <Input
                                        id="since_date"
                                        name="since_date"
                                        type="date"
                                        value={sinceDate}
                                        onChange={(e) => {
                                            setSinceDate(e.target.value);
                                            clearErrors('since_date');
                                        }}
                                        aria-invalid={!!errors.since_date}
                                    />
                                    {errors.since_date && (
                                        <p className="text-sm text-destructive">
                                            {errors.since_date}
                                        </p>
                                    )}
                                </div>

                                <div className="flex justify-end">
                                    <LoadingButton
                                        type="submit"
                                        loading={processing}
                                        loadingText="コミット収集中..."
                                        variant="default"
                                    >
                                        <RefreshCwIcon />
                                        コミット収集
                                    </LoadingButton>
                                </div>
                            </>
                        )}
                    </Form>
                </div>
            </PageLayout>
        </>
    );
}
