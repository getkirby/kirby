<template>
	<k-lab-examples>
		<k-lab-example label="Default">
			<k-block type="markdown" />
		</k-lab-example>
		<k-lab-example label="With Fieldset">
			<k-block :content="content" :fieldset="fieldset" :is-editable="true" :is-last-selected="true" :is-selected="true"
				type="markdown" @update="update" />
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
					text: "",
				},
			};
		},
		computed: {
			fieldset() {
				return {
					icon: "markdown",
					name: "Markdown",
					tabs: {
						main: {
							fields: {
								text: {
									label: "Markdown",
									type: "textarea",
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
