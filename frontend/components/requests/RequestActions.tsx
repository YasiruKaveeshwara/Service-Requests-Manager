"use client";

import { Button } from "@/components/ui/Button";
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
	if (abilities.start) actions.push({ key: "start", label: "Start work" });
	if (abilities.complete) actions.push({ key: "complete", label: "Mark completed" });
	if (actions.length === 0) return null;

	return (
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
	);
}
