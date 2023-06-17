<template>
	<k-models-dialog
		ref="dialog"
		:empty="{
			icon: 'page',
			text: $t('dialog.pages.empty')
		}"
		:fetch-params="fetch"
		@cancel="$emit('cancel')"
		@fetched="onFetched"
		@submit="$emit('submit', $event)"
	>
		<template v-if="model" #header>
			<header class="k-pages-dialog-navbar">
				<k-button
					:disabled="!model.id"
					:title="$t('back')"
					icon="angle-left"
					@click="back"
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
				@click.stop="go(page)"
			/>
		</template>
	</k-models-dialog>
</template>

<script>
export default {
	data() {
		return {
			model: {
				title: null,
				parent: null
			},
			parent: null
		};
	},
	computed: {
		fetch() {
			return { parent: this.parent };
		}
	},
	methods: {
		back() {
			this.parent = this.model.parent;
		},
		go(page) {
			this.parent = page.id;
		},
		onFetched(response) {
			this.model = response.model;
		},
		open(models, options) {
			this.parent = options?.parent;
			this.$refs.dialog.open(models, options);
		}
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
.k-pages-dialog-navbar .k-button[aria-disabled] {
	opacity: 0;
}
.k-pages-dialog-navbar .k-headline {
	flex-grow: 1;
	text-align: center;
}

.k-pages-dialog-option[aria-disabled] {
	opacity: 0.25;
}
</style>
