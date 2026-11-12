"use client";

import { useState } from "react";

import { DEMO_ACCOUNTS } from "@/lib/constants";
import { ApiError } from "@/lib/api";
import { Button } from "@/components/ui/Button";
import { FormField } from "@/components/ui/FormField";
import { Input } from "@/components/ui/Input";

export function LoginForm({ onSubmit }: { onSubmit: (email: string, password: string) => Promise<void> }) {
	const [email, setEmail] = useState("");
	const [password, setPassword] = useState("");
	const [error, setError] = useState<string | null>(null);
	const [fieldErrors, setFieldErrors] = useState<Record<string, string[]>>({});
	const [busy, setBusy] = useState(false);

	async function handleSubmit(event: React.FormEvent<HTMLFormElement>) {
		event.preventDefault();
		setError(null);
		setFieldErrors({});
		setBusy(true);

		try {
			await onSubmit(email, password);
		} catch (reason) {
			if (reason instanceof ApiError) {
				if (reason.status === 422) setFieldErrors(reason.errors ?? {});
				else setError(reason.message);
			} else {
				setError("Unable to sign in.");
			}
		} finally {
			setBusy(false);
		}
	}

	return (
		<div className='w-full max-w-md'>
			<form className='space-y-5 rounded-lg border border-slate-200 bg-white p-6 shadow-sm' onSubmit={handleSubmit}>
				<div>
					<h1 className='text-2xl font-semibold text-slate-900'>Sign in</h1>
					<p className='mt-1 text-sm text-slate-500'>Access your service requests.</p>
				</div>
				{error && (
					<p className='rounded-md bg-red-50 p-3 text-sm text-red-700' role='alert'>
						{error}
					</p>
				)}
				<FormField label='Email' error={fieldErrors.email?.[0]}>
					<Input
						aria-label='Email'
						type='email'
						value={email}
						onChange={(event) => setEmail(event.target.value)}
						required
						invalid={Boolean(fieldErrors.email)}
					/>
				</FormField>
				<FormField label='Password' error={fieldErrors.password?.[0]}>
					<Input
						aria-label='Password'
						type='password'
						value={password}
						onChange={(event) => setPassword(event.target.value)}
						required
						invalid={Boolean(fieldErrors.password)}
					/>
				</FormField>
				<Button type='submit' className='w-full' loading={busy}>
					Sign in
				</Button>
			</form>
			<div className='mt-4 rounded-lg border border-slate-200 bg-white p-5'>
				<h2 className='font-medium text-slate-900'>Demo accounts</h2>
				<div className='mt-3 space-y-2'>
					{DEMO_ACCOUNTS.map((account) => (
						<button
							key={account.email}
							type='button'
							className='block w-full rounded-md border border-slate-200 px-3 py-2 text-left text-sm text-slate-700 hover:bg-slate-50'
							onClick={() => {
								setEmail(account.email);
								setPassword(account.password);
								setError(null);
								setFieldErrors({});
							}}>
							{account.label}: {account.email}
						</button>
					))}
				</div>
				<p className='mt-3 text-xs text-slate-500'>Demo only. Password for all accounts: password.</p>
			</div>
		</div>
	);
}
