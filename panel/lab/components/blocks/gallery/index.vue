<template>
	<k-lab-examples>
		<k-lab-example label="Default">
			<k-block :content="content" type="gallery" @update="update" />
		</k-lab-example>
		<k-lab-example label="Disabled">
			<k-block
				:content="content"
				:disabled="true"
				type="gallery"
				@update="update"
			/>
		</k-lab-example>
	</k-lab-examples>
</template>

<script>
export default {
	data() {
		return {
			content: {
				caption: "",
				images: []
			}
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
