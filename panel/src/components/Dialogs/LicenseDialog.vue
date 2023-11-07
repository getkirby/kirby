<template>
	<k-dialog
		ref="dialog"
		v-bind="$props"
		@cancel="$emit('cancel')"
		@submit="$emit('submit')"
	>
		<k-bar style="margin-bottom: var(--spacing-2)">
			<h2 class="k-headline">
				{{ $t("license") }}
			</h2>
			<k-button
				icon="edit"
				link="https://hub.getkirby.com"
				target="_blank"
				variant="filled"
				size="xs"
			>
				Manage license
			</k-button>
		</k-bar>

		<div class="k-table">
			<table style="table-layout: auto">
				<tbody>
					<tr>
						<th>Type</th>
						<td>{{ license.type }}</td>
					</tr>
					<tr>
						<th>Key</th>
						<td class="k-text">
							<code>{{ license.license }}</code>
						</td>
					</tr>
					<tr>
						<th>Purchased</th>
						<td>{{ license.date }}</td>
					</tr>
					<tr>
						<th>Activated</th>
						<td>{{ license.activation }}</td>
					</tr>
					<tr>
						<th>Domain</th>
						<td>{{ license.domain }}</td>
					</tr>
				</tbody>
			</table>
		</div>
	</k-dialog>
</template>

<script>
import Dialog from "@/mixins/dialog.js";

export const props = {
	mixins: [Dialog],
	props: {
		license: Object
	}
};

export default {
	mixins: [props]
};
</script>
