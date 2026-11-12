"use client";

import { useState } from "react";

import { formatDateTime } from "@/lib/format";
import type { Comment } from "@/lib/types";
import { Badge } from "@/components/ui/Badge";
import { Button } from "@/components/ui/Button";
import { Textarea } from "@/components/ui/Textarea";

export function CommentSection({
	comments,
	canComment,
	onAdd,
	busy,
}: {
	comments: Comment[];
	canComment: boolean;
	onAdd: (body: string) => void;
	busy: boolean;
}) {
	const [body, setBody] = useState("");
	function add() {
		if (!body.trim()) return;
		onAdd(body);
		setBody("");
	}

	return (
		<section className='space-y-4'>
			<h2 className='text-lg font-semibold'>Comments</h2>
			{comments.map((comment) => (
				<article key={comment.id} className='border-b border-slate-100 pb-3'>
					<div className='flex items-center gap-2 text-sm'>
						<strong>{comment.user?.name ?? "Unknown"}</strong>
						{comment.user && <Badge className='bg-slate-100 text-slate-600'>{comment.user.role_label}</Badge>}
						<time className='text-xs text-slate-400'>{formatDateTime(comment.created_at)}</time>
					</div>
					<p className='mt-1 text-sm text-slate-700'>{comment.body}</p>
				</article>
			))}
			{canComment ?
				<div className='space-y-2'>
					<Textarea
						aria-label='New comment'
						value={body}
						onChange={(event) => setBody(event.target.value)}
						placeholder='Add a comment'
					/>
					<Button loading={busy} onClick={add}>
						Add comment
					</Button>
				</div>
			:	<p className='text-sm text-slate-500'>Comments are closed for this request.</p>}
		</section>
	);
}
