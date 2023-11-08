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
			<k-button
				v-bind="btn"
				link="https://hub.getkirby.com"
				target="_blank"
				variant="filled"
				size="xs"
			/>
		</k-bar>

		<div class="k-table">
			<table style="table-layout: auto">
				<tbody>
					<tr>
						<th>{{ $t("license.type") }}</th>
						<td>{{ license.type }}</td>
					</tr>
					<tr>
						<th>{{ $t("license.code") }}</th>
						<td class="k-text">
							<code>{{ license.code }}</code>
						</td>
					</tr>
					<tr>
						<th>{{ $t("license.purchased") }}</th>
						<td>{{ license.purchased }}</td>
					</tr>
					<tr>
						<th>{{ $t("license.activated") }}</th>
						<td>{{ license.activated }}</td>
					</tr>
					<tr>
						<th>{{ $t("license.domain") }}</th>
						<td>{{ license.domain }}</td>
					</tr>
					<tr>
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
	mixins: [props],
	computed: {
		btn() {
			if (this.license.status !== "active") {
				return {
					icon: "refresh",
					theme: this.license.theme,
					text: this.$t("license.renew")
				};
			}

			return {
				icon: "edit",
				text: this.$t("license.manage")
			};
		}
	}
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
