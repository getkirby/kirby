<template>
	<k-lab-examples>
		<k-lab-example label="Default">
			<k-block type="default" />
		</k-lab-example>
		<k-lab-example label="Disabled">
			<k-block :disabled="true" type="default" />
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
			content: {}
		};
	},
	methods: {
		update(content) {
			this.content = {
				...this.content,
				...content
			};
		}
	}
};
</script>
