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
						<th>{{ $t("license.type") }}</th>
						<td>{{ license.type }}</td>
					</tr>
					<tr v-if="license.code">
						<th>{{ $t("license.code") }}</th>
						<td class="k-text">
							<code>{{ license.code }}</code>
						</td>
					</tr>
					<tr v-if="license.info">
						<th>{{ $t("license.status") }}</th>
						<td :data-theme="license.theme">
							<p class="k-license-dialog-status">
								<k-icon :type="license.icon" />
								{{ license.info }}
							</p>
						</td>
					</tr>
				</tbody>
			</table>
		</div>

		<section class="k-license-dialog-upgrade"></section>
	</k-dialog>
</template>

<script>
import Dialog from "@/mixins/dialog.js";

export const props = {
	mixins: [Dialog],
	props: {
		license: Object,
		// eslint-disable-next-line vue/require-prop-types
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
	mixins: [props]
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
