<template>
	<label
		class="k-choice-input"
		:data-theme="theme"
		:data-has-info="Boolean(info)"
	>
		<k-choice v-bind="$props" @input="$emit('input', $event)" />
		<span class="k-choice-input-text">
			<span class="k-choice-input-label" v-html="label" />
			<span v-if="info" class="k-choice-input-info" v-html="info" />
		</span>
	</label>
</template>

<script>
import { props as Choice } from "@/components/Forms/Element/Choice.vue";
import { label } from "@/mixins/props.js";

export default {
	mixins: [Choice, label],
	emits: ["input"],
	props: {
		info: {
			type: String
		},
		theme: {
			type: String
		}
	}
};
</script>

<style>
:root {
	--choice-input-color-info: var(--color-text-dimmed);
}

.k-choice-input {
	display: flex;
	align-items: center;
	gap: var(--spacing-2);
}
.k-choice-input[data-has-info] {
	align-items: flex-start;
}
.k-choice-input[data-has-info] .k-choice {
	top: 1px;
}
.k-choice-input-text {
	display: flex;
	line-height: var(--leading-tight);
	flex-direction: column;
}
.k-choice-input-info {
	color: var(--color-text-dimmed);
}

.k-choice-input:not([data-has-info]) .k-choice-input-text {
	overflow: clip;
}

.k-choice-input[data-theme="field"] {
	background: var(--field-input-background);
	height: var(--field-input-height);
	padding-inline: var(--spacing-3);
	border-radius: var(--rounded);
	box-shadow: var(--shadow);
}
.k-choice-input[data-theme="field"][data-has-info] {
	height: auto;
	padding-block: var(--spacing-3);
}
</style>
