"use client";

import { useState } from "react";

import { CATEGORY_OPTIONS, PRIORITY_OPTIONS } from "@/lib/constants";
import type { ServiceRequestInput } from "@/lib/types";
import { Button } from "@/components/ui/Button";
import { FormField } from "@/components/ui/FormField";
import { Input } from "@/components/ui/Input";
import { Select } from "@/components/ui/Select";
import { Textarea } from "@/components/ui/Textarea";

const defaults: ServiceRequestInput = {
	title: "",
	description: "",
	category: "it",
	priority: "medium",
};

export function RequestForm({
	initial,
	mode,
	busy,
	errors,
	onSave,
	onSaveAndSubmit,
}: {
	initial?: Partial<ServiceRequestInput>;
	mode: "create" | "edit";
	busy: boolean;
	errors: Record<string, string[]> | null;
	onSave: (values: ServiceRequestInput) => void;
	onSaveAndSubmit?: (values: ServiceRequestInput) => void;
}) {
	const [values, setValues] = useState<ServiceRequestInput>({ ...defaults, ...initial });
	const [requiredError, setRequiredError] = useState<string | null>(null);

	function update<K extends keyof ServiceRequestInput>(key: K, value: ServiceRequestInput[K]) {
		setValues((current) => ({ ...current, [key]: value }));
	}

	function validate(): boolean {
		if (!values.title.trim() || !values.description.trim()) {
			setRequiredError("Title and description are required.");
			return false;
		}
		setRequiredError(null);
		return true;
	}

	function save(submit = false) {
		if (!validate()) return;
		if (submit && onSaveAndSubmit) onSaveAndSubmit(values);
		else onSave(values);
	}

	return (
		<form
			className='space-y-5 rounded-lg border border-slate-200 bg-white p-6 shadow-sm'
			onSubmit={(event) => {
				event.preventDefault();
				save();
			}}>
			<FormField label='Title' error={errors?.title?.[0]}>
				<Input
					maxLength={150}
					value={values.title}
					onChange={(event) => update("title", event.target.value)}
					invalid={Boolean(errors?.title)}
				/>
			</FormField>
			<FormField label='Description' error={errors?.description?.[0] ?? requiredError ?? undefined}>
				<Textarea
					maxLength={5000}
					rows={6}
					value={values.description}
					onChange={(event) => update("description", event.target.value)}
					invalid={Boolean(errors?.description || requiredError)}
				/>
			</FormField>
			<FormField label='Category' error={errors?.category?.[0]}>
				<Select
					value={values.category}
					onChange={(event) => update("category", event.target.value as ServiceRequestInput["category"])}
					invalid={Boolean(errors?.category)}>
					{CATEGORY_OPTIONS.map((option) => (
						<option key={option.value} value={option.value}>
							{option.label}
						</option>
					))}
				</Select>
			</FormField>
			<FormField label='Priority' error={errors?.priority?.[0]}>
				<Select
					value={values.priority}
					onChange={(event) => update("priority", event.target.value as ServiceRequestInput["priority"])}
					invalid={Boolean(errors?.priority)}>
					{PRIORITY_OPTIONS.map((option) => (
						<option key={option.value} value={option.value}>
							{option.label}
						</option>
					))}
				</Select>
			</FormField>
			<div className='flex flex-wrap gap-2'>
				<Button type='submit' loading={busy}>
					{mode === "create" ? "Save draft" : "Save changes"}
				</Button>
				{onSaveAndSubmit && (
					<Button type='button' variant='secondary' disabled={busy} onClick={() => save(true)}>
						Submit for review
					</Button>
				)}
			</div>
		</form>
	);
}
