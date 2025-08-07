<template>
	<k-models-dialog
		v-bind="$props"
		:fetch-params="{ parent: parent }"
		@cancel="$emit('cancel')"
		@fetched="model = $event.model"
		@submit="$emit('submit', $event)"
	>
		<template v-if="model" #header>
			<header class="k-pages-dialog-navbar">
				<k-button
					:disabled="!model.id"
					:title="$t('back')"
					icon="angle-left"
					@click="parent = model.parent"
				/>
				<k-headline>{{ model.title }}</k-headline>
			</header>
		</template>

		<template v-if="model" #options="{ item: page }">
			<k-button
				:disabled="!page.hasChildren"
				:title="$t('open')"
				icon="angle-right"
				class="k-pages-dialog-option"
				@click.stop="parent = page.id"
			/>
		</template>
	</k-models-dialog>
</template>

<script>
import Dialog from "@/mixins/dialog.js";
import { props as ModelsDialogProps } from "./ModelsDialog.vue";

export default {
	mixins: [Dialog, ModelsDialogProps],
	props: {
		empty: {
			type: Object,
			default: () => ({
				icon: "page",
				text: window.panel.t("dialog.pages.empty")
			})
		}
	},
	emits: ["cancel", "submit"],
	data() {
		return {
			model: null,
			parent: null
		};
	}
};
</script>

<style>
.k-pages-dialog-navbar {
	display: flex;
	align-items: center;
	justify-content: center;
	margin-bottom: 0.5rem;
	padding-inline-end: 38px;
}
.k-pages-dialog-navbar .k-button[aria-disabled="true"] {
	opacity: 0;
}
.k-pages-dialog-navbar .k-headline {
	flex-grow: 1;
	text-align: center;
}

.k-pages-dialog-option[aria-disabled="true"] {
	opacity: 0.25;
}
</style>
