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
		<div class="k-block-type-heading-level">
			<k-input
				ref="level"
				:empty="false"
				:options="levels"
				:value="content.level"
				type="select"
				@input="update({ level: $event })"
			/>
		</div>
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
	line-height: 1.25em;
	font-weight: var(--font-bold);
}
.k-block-type-heading-input[data-level="h1"] {
	font-size: var(--text-3xl);
	line-height: 1.125em;
}
.k-block-type-heading-input[data-level="h2"] {
	font-size: var(--text-2xl);
}
.k-block-type-heading-input[data-level="h3"] {
	font-size: var(--text-xl);
}
.k-block-type-heading-input[data-level="h4"] {
	font-size: var(--text-lg);
}
.k-block-type-heading-input[data-level="h5"] {
	line-height: 1.5em;
	font-size: var(--text-base);
}
.k-block-type-heading-input[data-level="h6"] {
	line-height: 1.5em;
	font-size: var(--text-sm);
}
.k-block-type-heading-input .ProseMirror strong {
	font-weight: 700;
}
.k-block-type-heading-level {
	font-size: var(--text-sm);
	font-weight: var(--font-bolder);
	position: absolute;
	inset-inline-end: 0;
	bottom: 0;
	top: 50%;
	transform: translateY(-50%);
}
.k-block-type-heading-level .k-select-input {
	position: relative;
	padding: 0.325rem 0.75rem 0.5rem 2rem;
	z-index: 1;
}
</style>
