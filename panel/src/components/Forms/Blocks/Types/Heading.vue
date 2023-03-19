<template>
	<div :data-level="content.level" class="k-block-type-heading-input">
		<k-writer
			ref="input"
			:inline="true"
			:marks="textField.marks"
			:placeholder="textField.placeholder"
			:value="content.text"
			@input="update({ text: $event })"
		/>
		<k-input
			ref="level"
			:empty="false"
			:options="levels"
			:value="content.level"
			type="select"
			class="k-block-type-heading-level"
			@input="update({ level: $event })"
		/>
	</div>
</template>

<script>
/**
 * @displayName BlockTypeHeading
 * @internal
 */
export default {
	computed: {
		levels() {
			let options = this.field("level", { options: [] }).options;
			return options.map((heading) => {
				heading.text = heading.text.toUpperCase();
				return heading;
			});
		},
		textField() {
			return this.field("text", {
				marks: true
			});
		}
	},
	methods: {
		focus() {
			this.$refs.input.focus();
		}
	}
};
</script>

<style>
.k-block-type-heading-input {
	display: flex;
	align-items: center;
	line-height: 1.25em;
	font-size: var(--text-size);
	font-weight: var(--font-bold);
}
.k-block-type-heading-input[data-level="h1"] {
	--text-size: var(--text-3xl);
	line-height: 1.125em;
}
.k-block-type-heading-input[data-level="h2"] {
	--text-size: var(--text-2xl);
}
.k-block-type-heading-input[data-level="h3"] {
	--text-size: var(--text-xl);
}
.k-block-type-heading-input[data-level="h4"] {
	--text-size: var(--text-lg);
}
.k-block-type-heading-input[data-level="h5"] {
	--text-size: var(--text-md);
	line-height: 1.5em;
}
.k-block-type-heading-input[data-level="h6"] {
	--text-size: var(--text-sm);
	line-height: 1.5em;
}
.k-block-type-heading-input .ProseMirror strong {
	font-weight: 700;
}
.k-block-type-heading-level {
	font-size: var(--text-sm);
	font-weight: var(--font-bold);
	color: var(--color-gray-500);
}
</style>
