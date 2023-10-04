<template>
	<k-ui-examples>
		<k-ui-example label="Default">
			<k-block :content="content" type="heading" @update="update" />
		</k-ui-example>
		<k-ui-example label="With Fieldset">
			<k-block
				:content="content"
				:fieldset="fieldset"
				:is-editable="true"
				:is-last-selected="true"
				:is-selected="true"
				type="heading"
				@update="update"
			/>
		</k-ui-example>
		<k-ui-example label="Output">
			<k-code>{{ content }}</k-code>
		</k-ui-example>
	</k-ui-examples>
</template>

<script>
export default {
	data() {
		return {
			content: {
				level: "h1",
				text: "",
			},
		};
	},
	computed: {
		fieldset() {
			return {
				icon: "title",
				name: "Heading",
				tabs: {
					main: {
						fields: {
							level: {
								label: "Level",
								type: "select",
								options: [
									{ text: "h1", value: "h1" },
									{ text: "h2", value: "h2" },
									{ text: "h3", value: "h3" },
								],
							},
							text: {
								label: "Text",
								type: "writer",
								placeholder: "Heading …",
							},
						},
					},
				},
			};
		},
	},
	methods: {
		update(content) {
			this.content = {
				...this.content,
				...content,
			};
		},
	},
};
</script>
