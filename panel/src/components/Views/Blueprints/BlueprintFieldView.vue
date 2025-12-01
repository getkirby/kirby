<template>
	<k-panel-inside class="k-blueprint-field-view">
		<k-header>
			{{ label }}
		</k-header>

		<k-stack gap="var(--spacing-12)">
			<k-stack>
				<k-headline>Field</k-headline>
				<k-definitions>
					<k-definition term="Name">{{ name }}</k-definition>
					<k-definition term="Type">{{ type }}</k-definition>
				</k-definitions>
			</k-stack>

			<k-stack>
				<k-headline>Settings</k-headline>
				<k-definitions>
					<k-definition
						v-for="prop in props"
						:key="prop.name"
						:term="prop.name"
						:description="prop.value"
					/>
				</k-definitions>
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
		label: String,
		name: String,
		type: String,
		props: Object
	},
	computed: {
		columns() {
			return {
				name: {
					type: "text",
					width: "10rem"
				},
				value: {
					type: "text"
				}
			};
		},
		rows() {
			return Object.values(this.props).map((prop) => {
				prop.type = prop.type
					.split("|")
					.map((type) => {
						return `<code data-type="${type}">${type}</code>`;
					})
					.join("");

				return prop;
			});
		}
	}
};
</script>

<style>
.k-blueprint-field-view .k-table {
	font-family: var(--font-mono);
}
.k-blueprint-field-view .k-header code {
	font-size: var(--text-sm);
}
</style>
