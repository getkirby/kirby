<template>
	<k-dialog
		ref="dialog"
		v-bind="$props"
		class="k-license-dialog"
		@cancel="$emit('cancel')"
		@submit="$emit('submit')"
	>
		<k-bar style="margin-bottom: var(--spacing-2)">
			<h2 class="k-headline">
				{{ $t("license") }}
			</h2>
		</k-bar>

		<div class="k-table">
			<table style="table-layout: auto">
				<tbody>
					<tr>
						<th data-mobile="true">{{ $t("type") }}</th>
						<td data-mobile="true">{{ license.type }}</td>
					</tr>
					<tr v-if="license.code">
						<th data-mobile="true">{{ $t("license.code") }}</th>
						<td data-mobile="true" class="k-text">
							<code>{{ license.code }}</code>
						</td>
					</tr>
					<tr v-if="license.info">
						<th data-mobile="true">{{ $t("status") }}</th>
						<td data-mobile="true" :data-theme="license.theme">
							<p class="k-license-dialog-status">
								<k-icon :type="license.icon" />
								{{ license.info }}
							</p>
						</td>
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
		license: Object,
		size: {
			default: "large"
		}
	}
};

/**
 * The license dialog is an internal dialog to show
 * the current state of the activated license.
 */
export default {
	mixins: [props],
	emits: ["cancel", "submit"]
};
</script>

<style>
.k-license-dialog-status {
	display: flex;
	align-items: center;
	gap: var(--spacing-2);
}

.k-license-dialog .k-icon {
	color: var(--theme-color-700);
}
</style>
