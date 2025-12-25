<template>
	<k-dialog
		ref="dialog"
		v-bind="$props"
		class="k-license-dialog"
		@cancel="$emit('cancel')"
		@submit="$emit('submit')"
	>
		<k-stack>
			<k-bar>
				<h2 class="k-headline">
					{{ $t("license") }}
				</h2>

				<k-button
					:text="$t('remove')"
					icon="trash"
					size="xs"
					variant="filled"
					dialog="license/remove"
				/>
			</k-bar>

			<k-definitions>
				<k-definition :term="$t('type')">
					<p class="k-license-dialog-type">
						{{ license.type }}
						<k-button
							icon="info"
							link="https://getkirby.com/license"
							target="_blank"
							theme="passive"
							size="xs"
						/>
					</p>
				</k-definition>
				<k-definition v-if="license.code" :term="$t('license.code')">
					<k-code-token type="pink">{{ license.code }}</k-code-token>
				</k-definition>
				<k-definition
					v-if="license.domain"
					:term="$t('domain')"
					:description="license.domain"
				/>
				<k-definition v-if="license.info" :term="$t('status')">
					<p :data-theme="license.theme">
						<k-icon :type="license.icon" />
						<k-text :html="license.info" />
					</p>
				</k-definition>
			</k-definitions>

			<k-text class="k-help">
				Manage your licenses on our <a href="">license hub</a>
			</k-text>
		</k-stack>
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
	mixins: [props]
};
</script>

<style>
.k-license-dialog .k-definition p {
	--icon-color: var(--theme-color-700);

	display: flex;
	align-items: center;
	justify-content: space-between;

	gap: var(--spacing-2);
}
.k-license-dialog-type {
	width: 100%;
}
</style>
