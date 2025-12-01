<template>
	<k-panel-inside class="k-blueprint-view">
		<k-header>{{ title }}</k-header>

		<k-stack gap="var(--spacing-12)">
			<k-stack>
				<k-headline>Blueprint</k-headline>
				<k-definitions>
					<k-definition term="ID">{{ id }}</k-definition>
					<k-definition term="Name">{{ name }}</k-definition>
					<k-definition term="Title">{{ title }}</k-definition>
					<k-definition term="Icon">{{ icon }}</k-definition>
				</k-definitions>
			</k-stack>

			<k-stack>
				<k-headline>Tabs</k-headline>
				<k-stack direction="row">
					<k-button
						v-for="tab in tabs"
						:key="tab.name"
						:link="link + '/tabs/' + tab.name"
						class="k-blueprint-tab"
						variant="filled"
					>
						{{ tab.label }}
					</k-button>
				</k-stack>
			</k-stack>

			<k-stack>
				<k-headline>Fields</k-headline>
				<k-table :columns="columns" :rows="rows" />
			</k-stack>
		</k-stack>
	</k-panel-inside>
</template>

<script>
/**
 * @internal
 */
export default {
	props: {
		fields: Array,
		id: String,
		icon: String,
		link: String,
		name: String,
		tabs: Array,
		title: String
	},
	computed: {
		columns() {
			return {
				label: {
					label: "Label",
					type: "url"
				},
				name: {
					label: "Name",
					type: "tags"
				},
				type: {
					label: "Type",
					type: "tags"
				},
				required: {
					label: "Required",
					type: "toggle"
				}
			};
		},
		rows() {
			return this.fields;
		}
	}
};
</script>
