<template>
	<k-lab-examples>
		<k-lab-example label="Default">
			<k-block type="quote" />
		</k-lab-example>
		<k-lab-example label="With Fieldset">
			<k-block :content="content" :fieldset="fieldset" :is-editable="true" :is-last-selected="true" :is-selected="true"
				type="quote" @update="update" />
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
					citation: "",
				},
			};
		},
		computed: {
			fieldset() {
				return {
					icon: "quote",
					name: "Quote",
					tabs: {
						main: {
							fields: {
								text: {
									label: "Quote",
									type: "writer",
									placeholder: "Quote …",
								},
								citation: {
									label: "Citation",
									type: "writer",
									placeholder: "Citation …",
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
