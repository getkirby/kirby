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

			<k-button-group>
				<k-button
					icon="cart"
					size="xs"
					link="https://hub.getkirby.com"
					text="Buy upgrade"
					target="_blank"
					variant="filled"
				/>
				<k-button
					icon="refresh"
					size="xs"
					text="Renew"
					theme="positive"
					variant="filled"
				/>
			</k-button-group>
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
					<tr v-if="license.purchased">
						<th>{{ $t("license.purchased") }}</th>
						<td>{{ license.purchased }}</td>
					</tr>
					<tr v-if="license.activated">
						<th>{{ $t("license.activated") }}</th>
						<td>{{ license.activated }}</td>
					</tr>
					<tr v-if="license.domain">
						<th>{{ $t("license.domain") }}</th>
						<td>{{ license.domain }}</td>
					</tr>
					<tr v-if="license.info">
						<th>{{ $t("license.status") }}</th>
						<td>
							<p :data-theme="license.theme" class="k-license-dialog-status">
								{{ license.info }}
								<strong>
									<k-icon :type="license.icon" />
									{{ license.renewal }}
								</strong>
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
		cancelButton: {
			default: false
		},
		// eslint-disable-next-line vue/require-prop-types
		size: {
			default: "large"
		},
		// eslint-disable-next-line vue/require-prop-types
		submitButton: {
			default: false
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
.k-license-dialog-status strong {
	display: flex;
	align-items: center;
	gap: var(--spacing-2);
	font-weight: var(--font-normal);
	color: var(--theme-color-700);
}
</style>
