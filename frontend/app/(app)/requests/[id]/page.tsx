"use client";

import { useParams, useRouter } from "next/navigation";
import { useState } from "react";

import { ActionDialog } from "@/components/requests/ActionDialog";
import { ActivityTimeline } from "@/components/requests/ActivityTimeline";
import { CommentSection } from "@/components/requests/CommentSection";
import { PriorityBadge } from "@/components/requests/PriorityBadge";
import { RequestActions, type ActionKey } from "@/components/requests/RequestActions";
import { StatusBadge } from "@/components/requests/StatusBadge";
import { Card } from "@/components/ui/Card";
import { EmptyState } from "@/components/ui/EmptyState";
import { ErrorMessage } from "@/components/ui/ErrorMessage";
import { Spinner } from "@/components/ui/Spinner";
import { useRequest } from "@/hooks/useRequest";
import { useOperationsUsers } from "@/hooks/useOperationsUsers";
import {
	useAddComment,
	useAssignRequest,
	useCancelRequest,
	useChangeStatus,
	useDecideRequest,
	useSubmitRequest,
} from "@/hooks/useRequestActions";
import { ApiError } from "@/lib/api";
import { formatDateTime, humanize } from "@/lib/format";

export default function RequestDetailPage() {
	const id = Number(useParams<{ id: string }>().id);
	const router = useRouter();
	const query = useRequest(id);
	const [dialog, setDialog] = useState<ActionKey | null>(null);
	const submit = useSubmitRequest(id);
	const cancel = useCancelRequest(id);
	const decide = useDecideRequest(id);
	const assign = useAssignRequest(id);
	const changeStatus = useChangeStatus(id);
	const addComment = useAddComment(id);
	const operations = useOperationsUsers(dialog === "assign");

	if (query.isLoading)
		return (
			<div className='flex justify-center py-12'>
				<Spinner size='lg' />
			</div>
		);
	if (query.isError) {
		if (query.error instanceof ApiError && query.error.status === 403)
			return <ErrorMessage error={new Error("You do not have access to this request.")} />;
		if (query.error instanceof ApiError && query.error.status === 404) return <EmptyState title='Request not found.' />;
		return <ErrorMessage error={query.error} />;
	}
	if (!query.data) return null;
	const request = query.data.data;
	const busy =
		submit.isPending ||
		cancel.isPending ||
		decide.isPending ||
		assign.isPending ||
		changeStatus.isPending ||
		addComment.isPending;
	const actionError = [cancel, decide, assign, changeStatus].find((mutation) => mutation.error)?.error;

	function onAction(action: ActionKey) {
		if (action === "edit") {
			router.push(`/requests/${id}/edit`);
			return;
		}
		if (action === "submit") {
			submit.mutate();
			return;
		}
		setDialog(action);
	}

	function confirm(values: { comment?: string; assigneeId?: number }) {
		if (dialog === "cancel") cancel.mutate(values, { onSuccess: () => setDialog(null) });
		if (dialog === "approve" || dialog === "reject" || dialog === "return")
			decide.mutate({ decision: dialog, comment: values.comment }, { onSuccess: () => setDialog(null) });
		if (dialog === "assign" && values.assigneeId)
			assign.mutate({ assigneeId: values.assigneeId, comment: values.comment }, { onSuccess: () => setDialog(null) });
		if (dialog === "start" || dialog === "complete")
			changeStatus.mutate(
				{ status: dialog === "start" ? "in_progress" : "completed", comment: values.comment },
				{ onSuccess: () => setDialog(null) },
			);
	}

	const dialogText =
		dialog === "cancel" ? ["Withdraw request", "Withdraw request"]
		: dialog === "approve" ? ["Approve request", "Approve"]
		: dialog === "reject" ? ["Reject request", "Reject"]
		: dialog === "return" ? ["Return request", "Return"]
		: dialog === "assign" ? ["Assign owner", "Assign owner"]
		: dialog === "start" ? ["Start work", "Start work"]
		: ["Complete request", "Mark completed"];
	return (
		<div className='grid gap-6 lg:grid-cols-[minmax(0,2fr)_minmax(280px,1fr)]'>
			<div className='space-y-6'>
				<div className='space-y-3'>
					<div className='flex flex-wrap items-center gap-2'>
						<StatusBadge status={request.status} />
						<PriorityBadge priority={request.priority} />
						<span className='text-sm text-slate-500'>{request.category_label || humanize(request.category)}</span>
					</div>
					<h1 className='text-3xl font-semibold text-slate-900'>{request.title}</h1>
					<p className='whitespace-pre-wrap text-slate-700'>{request.description}</p>
				</div>
				<dl className='grid gap-3 text-sm sm:grid-cols-2'>
					<div>
						<dt className='text-slate-500'>Requester</dt>
						<dd>{request.requester?.name ?? "-"}</dd>
					</div>
					<div>
						<dt className='text-slate-500'>Assignee</dt>
						<dd>{request.assignee?.name ?? "-"}</dd>
					</div>
					<div>
						<dt className='text-slate-500'>Submitted</dt>
						<dd>{formatDateTime(request.submitted_at)}</dd>
					</div>
					<div>
						<dt className='text-slate-500'>Completed</dt>
						<dd>{formatDateTime(request.completed_at)}</dd>
					</div>
				</dl>
				<RequestActions abilities={request.abilities} onAction={onAction} busy={busy} />
				<CommentSection
					comments={request.comments ?? []}
					canComment={request.abilities.comment}
					onAdd={(body) => addComment.mutate({ body })}
					busy={addComment.isPending}
				/>
			</div>
			<Card title='Activity'>
				<ActivityTimeline items={request.activity ?? []} />
			</Card>
			<ActionDialog
				open={dialog !== null}
				title={dialogText[0]}
				confirmLabel={dialogText[1]}
				commentRequired={dialog === "reject" || dialog === "return"}
				assignees={dialog === "assign" ? operations.data?.data : undefined}
				busy={busy}
				error={actionError instanceof ApiError ? actionError : null}
				onConfirm={confirm}
				onClose={() => setDialog(null)}
			/>
		</div>
	);
}
