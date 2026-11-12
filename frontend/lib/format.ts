export function formatDateTime(iso: string | null): string {
	if (iso === null) {
		return "-";
	}

	return new Intl.DateTimeFormat("en-US", {
		dateStyle: "medium",
		timeStyle: "short",
	}).format(new Date(iso));
}

export function humanize(value: string): string {
	const normalized = value.replace(/_/g, " ").toLowerCase();
	return normalized.charAt(0).toUpperCase() + normalized.slice(1);
}
