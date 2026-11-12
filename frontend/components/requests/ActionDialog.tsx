"use client";

import { useState } from "react";

import { ApiError } from "@/lib/api";
import type { User } from "@/lib/types";
import { Button } from "@/components/ui/Button";
import { FormField } from "@/components/ui/FormField";
import { Modal } from "@/components/ui/Modal";
import { Select } from "@/components/ui/Select";
import { Textarea } from "@/components/ui/Textarea";

export function ActionDialog({
	open,
	title,
	description,
	confirmLabel,
	commentLabel = "Comment",
	commentRequired = false,
	assignees,
	busy,
	error,
	onConfirm,
	onClose,
}: {
	open: boolean;
	title: string;
	description?: string;
	confirmLabel: string;
	commentLabel?: string;
	commentRequired?: boolean;
	assignees?: User[];
	busy: boolean;
	error?: ApiError | null;
	onConfirm: (values: { comment?: string; assigneeId?: number }) => void;
	onClose: () => void;
}) {
	const [comment, setComment] = useState("");
	const [assigneeId, setAssigneeId] = useState("");
	const [localError, setLocalError] = useState<string | null>(null);

	function confirm() {
		if (commentRequired && !comment.trim()) {
			setLocalError(`${commentLabel} is required.`);
			return;
		}
		if (assignees && !assigneeId) {
			setLocalError("An owner is required.");
			return;
		}
		setLocalError(null);
		onConfirm({ comment: comment || undefined, assigneeId: assigneeId ? Number(assigneeId) : undefined });
	}

	return (
		<Modal
			open={open}
			title={title}
			onClose={onClose}
			footer={
				<>
					<Button variant='secondary' onClick={onClose}>
						Cancel
					</Button>
					<Button loading={busy} onClick={confirm}>
						{confirmLabel}
					</Button>
				</>
			}>
			{description && <p className='mb-4 text-sm text-slate-500'>{description}</p>}
			{error && (
				<p className='mb-4 text-sm text-red-600' role='alert'>
					{error.message}
				</p>
			)}
			{localError && (
				<p className='mb-4 text-sm text-red-600' role='alert'>
					{localError}
				</p>
			)}
			{assignees && (
				<FormField label='Owner' error={error?.errors?.assignee_id?.[0]}>
					<Select aria-label='Owner' value={assigneeId} onChange={(event) => setAssigneeId(event.target.value)}>
						<option value=''>Select an owner</option>
						{assignees.map((user) => (
							<option key={user.id} value={user.id}>
								{user.name}
							</option>
						))}
					</Select>
				</FormField>
			)}
			<div className={assignees ? "mt-4" : ""}>
				<FormField label={commentLabel} error={error?.errors?.comment?.[0]}>
					<Textarea aria-label={commentLabel} value={comment} onChange={(event) => setComment(event.target.value)} />
				</FormField>
			</div>
		</Modal>
	);
}
