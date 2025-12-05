<template>
	<k-lab-examples>
		<k-lab-example label="Default">
			<k-code>Hello world</k-code>
		</k-lab-example>
		<k-lab-example label="Language">
			<k-code language="html">{{ html }}</k-code>
		</k-lab-example>
		<k-lab-example label="Editing">
			<k-grid class="k-lab-code-editor" style="--columns: 2">
				<k-textarea-input
					:buttons="false"
					:value="code"
					font="monospace"
					@input="code = $event"
				/>
				<k-code language="html">{{ code }}</k-code>
			</k-grid>
		</k-lab-example>
	</k-lab-examples>
</template>

<script>
export default {
	data() {
		return {
			code: "Enter some HTML 🤯 …"
		};
	},
	computed: {
		html() {
			return `<p>Hello world</p>`;
		}
	}
};
</script>

<style>
.k-lab-code-editor {
	background: var(--code-color-back);
	color: var(--code-color-text);
	border-radius: var(--rounded);
}
.k-lab-code-editor .k-textarea-input {
	padding: var(--code-padding);
	border-right: 1px solid var(--color-border);
}
</style>
