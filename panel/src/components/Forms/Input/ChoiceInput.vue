<template>
	<label
		class="k-choice-input"
		:data-theme="theme"
		:data-has-info="Boolean(info)"
	>
		<k-choice
			ref="input"
			v-bind="$props"
			@input="$emit('input', $event)"
			@invalid="$emit('invalid', $event)"
		/>
		<span class="k-choice-input-label">
			<span class="k-choice-input-text" v-html="label" />
			<span v-if="info" class="k-choice-input-info" v-html="info" />
		</span>
	</label>
</template>

<script>
import { props as Choice } from "@/components/Forms/Element/Choice.vue";
import { label } from "@/mixins/props.js";
import { required as validateRequired } from "vuelidate/lib/validators";

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
}
.k-choice-input-label > * {
	display: block;
	overflow: hidden;
	text-overflow: ellipsis;
}
.k-choice-input-info {
	color: var(--choice-input-color-info);
}

.k-choice-input[data-theme="field"] {
	background: var(--choice-input-color-back);
	min-height: var(--field-input-height);
	padding-block: var(--spacing-2);
	padding-inline: var(--spacing-3);
	border-radius: var(--rounded);
	box-shadow: var(--shadow);
}
</style>
