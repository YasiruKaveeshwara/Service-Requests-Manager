import { STATUS_STYLES } from "@/lib/constants";
import { humanize } from "@/lib/format";
import type { RequestStatus } from "@/lib/types";
import { Badge } from "@/components/ui/Badge";

export function StatusBadge({ status }: { status: RequestStatus }) {
	return <Badge className={STATUS_STYLES[status]}>{humanize(status)}</Badge>;
}
