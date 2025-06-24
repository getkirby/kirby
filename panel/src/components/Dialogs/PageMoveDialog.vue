<template>
	<k-dialog
		ref="dialog"
		v-bind="$props"
		:submit-button="{
			icon: 'parent',
			text: $t('move')
		}"
		class="k-page-move-dialog"
		size="medium"
		@cancel="$emit('cancel')"
		@submit="$emit('submit', value)"
	>
		<k-headline>{{ $t("page.move") }}</k-headline>
		<div class="k-page-move-parent" tabindex="0" data-autofocus>
			<k-page-tree
				:current="value.parent"
				:move="value.move"
				identifier="id"
				@select="select"
			/>
		</div>
	</k-dialog>
</template>

<script>
import Dialog from "@/mixins/dialog.js";

/**
 * @since 4.0.0
 */
export default {
	mixins: [Dialog],
	props: {
		value: {
			default() {
				return {};
			},
			type: Object
		}
	},
	emits: ["cancel", "input", "submit"],
	methods: {
		select(page) {
			this.$emit("input", { ...this.value, parent: page.value });
		}
	}
};
</script>

<style>
.k-page-move-dialog .k-headline {
	margin-bottom: var(--spacing-2);
}
.k-page-move-parent {
	--tree-color-back: var(--input-color-back);
	--tree-branch-color-back: var(--input-color-back);
	--tree-branch-hover-color-back: var(--panel-color-back);
	padding: var(--spacing-3);
	background: var(--tree-color-back);
	border-radius: var(--rounded);
	box-shadow: var(--shadow);
}
</style>
