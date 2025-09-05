<template>
	<fieldset
		:disabled="disabled"
		:class="['k-toggles-input', $attrs.class]"
		:style="$attrs.style"
	>
		<legend class="sr-only">{{ $t("options") }}</legend>

		<k-input-validator :required="required" :value="JSON.stringify(value)">
			<ul
				:data-labels="labels"
				:style="{ '--options': columns ?? options.length }"
			>
				<li v-for="(option, index) in options" :key="index">
					<input
						:id="id + '-' + index"
						:aria-label="option.text"
						:disabled="disabled"
						:value="option.value"
						:name="id"
						:checked="value === option.value"
						class="input-hidden"
						type="radio"
						@click="onClick(option.value)"
						@change="onInput(option.value)"
					/>
					<label :for="id + '-' + index" :title="option.text">
						<k-icon v-if="option.icon" :type="option.icon" />
						<!-- eslint-disable vue/no-v-html -->
						<span
							v-if="labels || !option.icon"
							class="k-toggles-text"
							v-html="option.text"
						/>
						<!-- eslint-enable vue/no-v-html -->
					</label>
				</li>
			</ul>
		</k-input-validator>
	</fieldset>
</template>

<script>
import Input, { props as InputProps } from "@/mixins/input.js";

export const props = {
	mixins: [InputProps],
	props: {
		columns: Number,
		grow: Boolean,
		labels: Boolean,
		options: Array,
		reset: Boolean,
		value: [String, Number, Boolean]
	}
};

export default {
	mixins: [Input, props],
	emits: ["input"],
	mounted() {
		if (this.$props.autofocus) {
			this.focus();
		}
	},
	methods: {
		focus() {
			(
				this.$el.querySelector("input[checked]") ||
				this.$el.querySelector("input")
			)?.focus();
		},
		onClick(value) {
			if (value === this.value && this.reset && !this.required) {
				this.$emit("input", "");
			}
		},
		onInput(value) {
			this.$emit("input", value);
		},
		select() {
			this.focus();
		}
	}
};
</script>

<style>
.k-input[data-type="toggles"] {
	display: inline-flex;
}
.k-input[data-type="toggles"].grow {
	display: flex;
}
.k-input[data-type="toggles"]:has(.k-empty) {
	outline: 0;
	display: flex;
}

.k-toggles-input ul {
	display: grid;
	grid-template-columns: repeat(var(--options), minmax(0, 1fr));
	gap: 1px;
	border-radius: var(--rounded);
	line-height: 1;
	background: var(--color-border);
	overflow: hidden;
}

.k-toggles-input li {
	height: var(--field-input-height);
}
.k-toggles-input label {
	align-items: center;
	background: light-dark(var(--color-white), var(--color-gray-850));
	cursor: pointer;
	display: flex;
	font-size: var(--text-sm);
	justify-content: center;
	line-height: 1.25;
	padding: 0 var(--spacing-3);
	height: 100%;
}
.k-toggles-input[disabled] label {
	cursor: not-allowed;
}

.k-toggles-input input:checked + label {
	background: light-dark(var(--color-black), var(--color-gray-950));
	color: var(--color-white);
}
.k-toggles-input input:focus:not(:checked) + label {
	background: light-dark(var(--color-blue-300), var(--color-blue-800));
}
.k-toggles-input input[disabled]:checked + label {
	background: light-dark(var(--color-gray-600), var(--color-gray-850));
}
.k-toggles-input input[disabled]:not(:checked) + label {
	color: var(--color-text-dimmed);
	background: var(--panel-color-back);
}

.k-toggles-input .k-icon + .k-toggles-text {
	margin-inline-start: var(--spacing-2);
}
</style>
