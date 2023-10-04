<template>
	<k-ui-examples>
		<k-ui-example label="Default">
			<k-block type="code" />
		</k-ui-example>
		<k-ui-example label="With Fieldset">
			<k-block :content="content" :fieldset="fieldset" :is-editable="true" :is-last-selected="true" :is-selected="true"
				type="code" @update="update" />
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
					code: "",
					language: "",
				},
			};
		},
		computed: {
			fieldset() {
				return {
					icon: "code",
					name: "Code",
					tabs: {
						main: {
							fields: {
								language: {
									label: "Level",
									type: "select",
									options: [
										{ text: "PHP", value: "php" },
										{ text: "HTML", value: "html" },
										{ text: "CSS", value: "css" },
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
