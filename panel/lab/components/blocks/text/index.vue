<template>
	<k-lab-examples>
		<k-lab-example label="Default">
			<k-block :content="content" type="text" @update="update" />
		</k-lab-example>
		<k-lab-example label="With Fieldset">
			<k-block
				:content="content"
				:fieldset="fieldset"
				:is-editable="true"
				:is-last-selected="true"
				:is-selected="true"
				type="text"
				@update="update"
			/>
		</k-lab-example>
		<k-lab-example label="Output">
			<k-code>{{ content }}</k-code>
		</k-lab-example>
	</k-lab-examples>
</template>

<script>
export default {
	data() {
		return {
			content: {
				level: "",
				text: "",
			},
		};
	},
	computed: {
		fieldset() {
			return {
				icon: "text",
				name: "Text",
				tabs: {
					main: {
						fields: {
							text: {
								label: "Text",
								type: "writer",
								placeholder: "Text …",
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
