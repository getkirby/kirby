<template>
	<fieldset
		v-if="choices.length"
		:disabled="disabled"
		:data-grow="grow"
		class="k-toggles-input"
	>
		<legend class="sr-only">{{ $t("options") }}</legend>
		<ul :style="'--options:' + (columns ?? options.length)">
			<li v-for="(choice, index) in choices" :key="index">
				<input
					:aria-label="choice.label"
					:autofocus="choice.autofocus"
					:checked="choice.checked"
					:disabled="choice.disabled"
					:id="choice.id"
					:name="choice.name"
					:required="choice.required"
					:value="choice.value"
					class="input-hidden"
					type="radio"
					@click="toggle(choice.value)"
					@change="$emit('input', choice.value)"
				/>
				<label :for="choice.id" :title="choice.label">
					<k-icon v-if="choice.icon" :type="choice.icon" />
					<!-- eslint-disable vue/no-v-html -->
					<span
						v-if="labels || !choice.icon"
						class="k-toggles-text"
						v-html="choice.label"
					/>
					<!-- eslint-enable vue/no-v-html -->
				</label>
			</li>
		</ul>
	</fieldset>
</template>

<script>
import RadioInput, { props as RadioInputProps } from "./RadioInput.vue";

export const props = {
	mixins: [RadioInputProps],
	props: {
		grow: {
			default: false,
			type: Boolean
		},
		labels: {
			default: true,
			type: Boolean
		}
	}
};

export default {
	mixins: [RadioInput, props]
};
</script>

<style>
.k-toggles-input {
	display: inline-flex;
}
.k-toggles-input[data-grow="true"] {
	display: block;
}
.k-toggles-input ul {
	display: grid;
	grid-template-columns: repeat(var(--options), minmax(0, 1fr));
	gap: var(--spacing-2);
}
.k-toggles-input li {
	height: var(--inputbox-height);
}
.k-toggles-input label {
	align-items: center;
	cursor: pointer;
	display: flex;
	background: var(--color-white);
	justify-content: center;
	line-height: 1.25;
	padding: 0 var(--spacing-3);
	height: 100%;
	border-radius: var(--input-rounded);
	box-shadow: var(--shadow);
}
.k-toggles-input[disabled] label {
	background: none;
	cursor: not-allowed;
}
.k-toggles-input .k-icon + .k-toggles-text {
	margin-inline-start: var(--spacing-2);
}
.k-toggles-input input:focus + label {
	outline: var(--outline);
}
.k-toggles-input input:checked + label {
	background: var(--color-black);
	color: var(--color-white);
}
</style>
