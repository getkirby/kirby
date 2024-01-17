<template>
	<k-lab-examples>
		<k-lab-example label="Default">
			<k-block
				:content="content"
				:fieldset="fieldset"
				type="video"
				@update="update"
			/>
		</k-lab-example>
		<k-lab-example label="Selected">
			<k-block
				:content="content"
				:fieldset="fieldset"
				:is-editable="true"
				:is-last-selected="true"
				:is-selected="true"
				type="video"
				@update="update"
			/>
		</k-lab-example>
		<k-lab-example label="Disabled">
			<k-block
				:content="content"
				:disabled="true"
				:fieldset="fieldset"
				type="video"
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
	props: {
		defaults: Object,
		fieldset: Object
	},
	data() {
		return {
			content: this.defaults
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
