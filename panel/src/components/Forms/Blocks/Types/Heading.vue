<template>
	<div
		:class="['k-block-type-heading-input', $attrs.class]"
		:data-level="content.level"
		:style="$attrs.style"
	>
		<k-writer-input
			ref="input"
			v-bind="textField"
			:disabled="disabled"
			:inline="true"
			:keys="keys"
			:value="content.text"
			@input="update({ text: $event })"
		/>
		<k-input
			v-if="levels.length > 1"
			ref="level"
			:disabled="disabled"
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
import Block from "./Default.vue";

/**
 * @displayName BlockTypeHeading
 */
export default {
	extends: Block,
	inheritAttrs: false,
	emits: ["append", "open", "split", "update"],
	computed: {
		isSplitable() {
			return (
				this.content.text.length > 0 &&
				this.$refs.input.isCursorAtStart === false &&
				this.$refs.input.isCursorAtEnd === false
			);
		},
		keys() {
			return {
				Enter: () => {
					if (this.$refs.input.isCursorAtEnd === true) {
						return this.$emit("append", "text");
					}

					return this.split();
				},
				"Mod-Enter": this.split
			};
		},
		levels() {
			return this.field("level", { options: [] }).options;
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
		},
		merge(blocks) {
			this.update({
				text: blocks.map((block) => block.content.text).join(" ")
			});
		},
		split() {
			const contents = this.$refs.input.getSplitContent?.();

			if (contents) {
				this.$emit("split", [
					{ text: contents[0] },
					{
						// decrease heading level for newly created block
						level: "h" + Math.min(parseInt(this.content.level.slice(1)) + 1, 6),
						text: contents[1]
					}
				]);
			}
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
.k-block-type-heading-input .k-writer-input .ProseMirror strong {
	font-weight: 700;
}
.k-block-type-heading-level {
	--input-color-back: transparent;
	--input-color-border: none;
	--input-color-text: light-dark(var(--color-gray-600), var(--color-gray-500));
	font-weight: var(--font-bold);
	text-transform: uppercase;
}
</style>
