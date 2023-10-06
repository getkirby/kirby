<template>
	<k-lab-examples>
		<k-lab-example label="Default">
			<k-code>Hello world</k-code>
		</k-lab-example>
		<k-lab-example label="Language">
			<k-code language="html">{{ php }}</k-code>
		</k-lab-example>
	</k-lab-examples>
</template>

<script>
export default {
	computed: {
		php() {
			return `<p>Hello world</p>`;
		}
	}
};
</script>
