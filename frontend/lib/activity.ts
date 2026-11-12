import { humanize } from "@/lib/format";
import type { ActivityLog } from "@/lib/types";

export function describeActivity(log: ActivityLog): string {
	const actor = log.actor?.name ?? "Someone";
	switch (log.event) {
		case "created":
			return `${actor} created the request`;
		case "updated":
			return `${actor} edited the request`;
		case "transitioned":
			return `${actor} moved the request from ${humanize(log.from_status ?? "")} to ${humanize(log.to_status ?? "")}`;
		case "assigned":
			return `${actor} assigned the request to ${String(log.metadata?.to_assignee_name ?? "an owner")}`;
		case "reassigned":
			return `${actor} reassigned the request to ${String(log.metadata?.to_assignee_name ?? "an owner")}`;
		case "commented":
			return `${actor} commented`;
	}
}
