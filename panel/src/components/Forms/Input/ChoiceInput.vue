<template>
	<label
		class="k-choice-input"
		:aria-disabled="disabled"
		:data-has-info="Boolean(info)"
		:data-theme="theme"
	>
		<k-choice
			ref="input"
			v-bind="$props"
			@input="$emit('input', $event)"
			@invalid="$emit('invalid', $event)"
		/>
		<span class="k-choice-input-label">
			<!-- eslint-disable-next-line vue/no-v-html -->
			<span class="k-choice-input-text" v-html="label" />
			<!-- eslint-disable-next-line vue/no-v-html -->
			<span v-if="info" class="k-choice-input-info" v-html="info" />
		</span>
	</label>
</template>

<script>
import { props as Choice } from "@/components/Forms/Element/Choice.vue";
import { label } from "@/mixins/props.js";

export const props = {
	mixins: [Choice, label],
	props: {
		info: {
			type: String
		},
		theme: {
			type: String
		}
	}
};

export default {
	mixins: [props],
	emits: ["input", "invalid"],
	methods: {
		focus() {
			this.$refs.input.focus();
		},
		select() {
			this.focus();
		}
	}
};
</script>

<style>
:root {
	--choice-input-color-back: var(--color-white);
	--choice-input-color-info: var(--color-text-dimmed);
	--choice-input-color-text: var(--color-text);
}

.k-choice-input {
	display: flex;
	gap: var(--spacing-3);
	min-width: 0;
}
.k-choice-input .k-choice {
	top: 2px;
}
.k-choice-input-label {
	display: flex;
	line-height: 1.25rem;
	flex-direction: column;
	min-width: 0;
	color: var(--choice-input-color-text);
}
.k-choice-input-label > * {
	display: block;
	overflow: hidden;
	text-overflow: ellipsis;
}
.k-choice-input-info {
	color: var(--choice-input-color-info);
}

.k-choice-input[aria-disabled] {
	cursor: not-allowed;
	--choice-input-color-back: var(--color-light);
	--choice-input-color-info: var(--color-gray-400);
	--choice-input-color-text: var(--color-text-dimmed);
	--shadow: none;
}
</style>
