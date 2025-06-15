<template>
	<k-panel-inside class="k-lab-index-view">
		<k-header>
			Lab

			<template #buttons>
				<k-input
					type="search"
					icon="search"
					:placeholder="$t('filter') + ' …'"
					:value="q"
					@input="q = $event"
				/>
			</template>
		</k-header>

		<k-tabs
			:tab="tab"
			:tabs="[
				{ name: 'examples', label: 'Examples', link: '/lab' },
				{ name: 'docs', label: 'Docs', link: '/lab/docs' }
			]"
		/>

		<k-box v-if="info" icon="question" theme="info" :text="info" :html="true" />

		<k-section
			v-for="category in filteredCategories"
			:key="category.name"
			:headline="category.name"
		>
			<k-collection
				:items="category.examples"
				:empty="{
					icon: category.icon,
					text: 'Add examples to ' + category.path
				}"
			/>
		</k-section>
	</k-panel-inside>
</template>

<script>
export default {
	props: {
		categories: Array,
		info: String,
		tab: String
	},
	data() {
		return {
			q: ""
		};
	},
	computed: {
		filteredCategories() {
			if (!this.q) {
				return this.categories;
			}

			const categories = this.$helper.object.clone(this.categories);
			const query = this.q.toLowerCase();

			for (const category of categories) {
				category.examples = category.examples.filter((example) =>
					example.text.toLowerCase().includes(query)
				);
			}

			return categories.filter((category) => category.examples.length > 0);
		}
	}
};
</script>

<style>
.k-lab-index-view .k-panel-main > .k-header .k-input {
	--input-color-back: var(--color-border);
	--input-color-border: transparent;
	--input-height: var(--height-md);
	width: 40cqw;
	max-width: 20rem;
	transform: translateY(-0.5rem);
}
.k-lab-index-view .k-panel-main > .k-header > .k-header-buttons {
	margin-bottom: 0;
}
.k-lab-index-view .k-panel-main > .k-box {
	margin-bottom: var(--spacing-8);
}
.k-lab-index-view .k-list-items {
	grid-template-columns: repeat(auto-fill, minmax(12rem, 1fr));
}
</style>
