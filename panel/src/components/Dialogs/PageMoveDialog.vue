<template>
	<k-dialog
		ref="dialog"
		:submit-button="{
			icon: 'road-sign',
			text: $t('move')
		}"
		class="k-page-move-dialog"
		size="medium"
		v-bind="$props"
	>
		<k-headline>{{ $t("page.move") }}</k-headline>
		<div class="k-page-move-parent" tabindex="0" data-autofocus>
			<k-page-tree
				:current="$panel.dialog.value.parent"
				identifier="id"
				@select="select"
			/>
		</div>
	</k-dialog>
</template>

<script>
import Dialog from "@/mixins/dialog.js";

export default {
	mixins: [Dialog],
	methods: {
		select(page) {
			this.$panel.dialog.input({ parent: page.id });
		}
	}
};
</script>

<style>
.k-page-move-dialog .k-headline {
	margin-bottom: var(--spacing-2);
}
.k-page-move-parent {
	--tree-color-back: var(--color-white);
	--tree-color-hover-back: var(--color-light);
	padding: var(--spacing-3);
	background: var(--color-white);
	border-radius: var(--rounded);
	box-shadow: var(--shadow);
}
</style>
