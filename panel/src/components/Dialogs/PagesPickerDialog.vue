<template>
	<k-models-picker-dialog
		ref="dialog"
		v-bind="$props"
		:payload="payload"
		@cancel="$emit('cancel')"
		@submit="$emit('submit', $event)"
	>
		<template v-if="parent" #header>
			<header class="k-pages-dialog-navigation">
				<k-button
					:disabled="!parent.id"
					:title="$t('back')"
					icon="angle-left"
					size="sm"
					variant="filled"
					@click="navigate(parent.parent)"
				/>
				<k-headline>{{ parent.title }}</k-headline>
			</header>
		</template>

		<template v-if="parent" #options="{ item: page }">
			<k-button
				:disabled="!page.hasChildren"
				:title="$t('open')"
				icon="angle-right"
				class="k-pages-picker-dialog-option"
				@click.stop="navigate(page.id)"
			/>
		</template>
	</k-models-picker-dialog>
</template>

<script>
import { props as ModelsPickerDialog } from "./ModelsPickerDialog.vue";

export default {
	mixins: [ModelsPickerDialog],
	props: {
		empty: {
			type: Object,
			default: () => ({
				icon: "page",
				text: window.panel.t("dialog.pages.empty")
			})
		},
		/**
		 * Current (navigation) parent
		 */
		parent: {
			type: Object
		}
	},
	emits: ["cancel", "submit"],
	computed: {
		/**
		 * Payload to send along dialog refreshes
		 */
		payload() {
			return {
				parent: this.parent?.id
			};
		}
	},
	methods: {
		navigate(parent) {
			this.$refs.dialog.refresh({ parent });
		}
	}
};
</script>

<style>
.k-pages-dialog-navigation {
	display: flex;
	align-items: center;
	justify-content: center;
	margin-bottom: var(--spacing-3);
}
.k-pages-dialog-navigation .k-button[aria-disabled="true"] {
	opacity: 0;
}
.k-pages-dialog-navigation .k-headline {
	flex-grow: 1;
	text-align: center;
}
.k-pages-picker-dialog-option[aria-disabled="true"] {
	opacity: 0.25;
}
</style>
