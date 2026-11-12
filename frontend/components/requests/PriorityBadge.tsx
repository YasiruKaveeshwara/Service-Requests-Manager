import { PRIORITY_STYLES } from "@/lib/constants";
import { humanize } from "@/lib/format";
import type { Priority } from "@/lib/types";
import { Badge } from "@/components/ui/Badge";

export function PriorityBadge({ priority }: { priority: Priority }) {
	return <Badge className={PRIORITY_STYLES[priority]}>{humanize(priority)}</Badge>;
}
