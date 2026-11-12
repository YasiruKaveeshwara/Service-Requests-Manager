"use client";

import { useState } from "react";

import { Button } from "@/components/ui/Button";
import { Select } from "@/components/ui/Select";
import type { Abilities } from "@/lib/types";

export type ActionKey =
	| "edit"
	| "submit"
	| "cancel"
	| "approve"
	| "reject"
	| "return"
	| "assign"
	| "start"
	| "complete";

export function RequestActions({
	abilities,
	onAction,
	busy = false,
}: {
	abilities: Abilities;
	onAction: (action: ActionKey) => void;
	busy?: boolean;
}) {
	const actions: { key: ActionKey; label: string; variant?: "primary" | "secondary" | "danger" | "ghost" }[] = [];
	if (abilities.update) actions.push({ key: "edit", label: "Edit", variant: "secondary" });
	if (abilities.submit) actions.push({ key: "submit", label: "Submit for review" });
	if (abilities.cancel) actions.push({ key: "cancel", label: "Withdraw", variant: "danger" });
	if (abilities.decide)
		actions.push(
			{ key: "approve", label: "Approve" },
			{ key: "return", label: "Return", variant: "secondary" },
			{ key: "reject", label: "Reject", variant: "danger" },
		);
	if (abilities.assign) actions.push({ key: "assign", label: "Assign owner", variant: "secondary" });
	const statusOptions: { key: "start" | "complete"; label: string }[] = [];
	if (abilities.start) statusOptions.push({ key: "start", label: "In progress" });
	if (abilities.complete) statusOptions.push({ key: "complete", label: "Completed" });
	const [statusAction, setStatusAction] = useState<"start" | "complete" | "">("");
	if (actions.length === 0 && statusOptions.length === 0) return null;

	return (
		<div className='space-y-3'>
			{actions.length > 0 && (
				<div className='flex flex-wrap gap-2'>
					{actions.map((action) => (
						<Button
							key={action.key}
							data-action={action.key}
							variant={action.variant}
							disabled={busy}
							onClick={() => onAction(action.key)}>
							{action.label}
						</Button>
					))}
				</div>
			)}
			{statusOptions.length > 0 && (
				<div className='flex flex-wrap items-end gap-2'>
					<label className='text-sm font-medium text-slate-700'>
						<span className='mb-1 block'>Change status</span>
						<Select
							aria-label='Change status'
							value={statusAction}
							disabled={busy}
							onChange={(event) => setStatusAction(event.target.value as "start" | "complete" | "")}>
							<option value=''>Select a status</option>
							{statusOptions.map((option) => (
								<option key={option.key} value={option.key}>
									{option.label}
								</option>
							))}
						</Select>
					</label>
					<Button
						data-action='change-status'
						disabled={busy || statusAction === ""}
						onClick={() => {
							if (statusAction) onAction(statusAction);
						}}>
						Apply status
					</Button>
				</div>
			)}
		</div>
	);
}
