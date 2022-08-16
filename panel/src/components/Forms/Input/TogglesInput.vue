<template>
	<ul
		:data-invalid="$v.$invalid"
		:data-labels="labels"
		:style="'--options:' + (columns || options.length)"
		class="k-toggles-input"
	>
		<li v-for="(option, index) in options" :key="index">
			<input
				:id="id + '-' + index"
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
				<span v-if="labels" class="k-toggles-text">
					{{ option.text }}
				</span>
			</label>
		</li>
	</ul>
</template>

<script>
import { autofocus, disabled, id, required } from "@/mixins/props.js";
import { required as validateRequired } from "vuelidate/lib/validators";

export const props = {
	mixins: [autofocus, disabled, id, required],
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
	mixins: [props],
	inheritAttrs: false,
	watch: {
		value() {
			this.onInvalid();
		}
	},
	mounted() {
		this.onInvalid();

		if (this.$props.autofocus) {
			this.focus();
		}
	},
	methods: {
		focus() {
			(
				this.$el.querySelector("input[checked]") ||
				this.$el.querySelector("input")
			).focus();
		},
		onClick(value) {
			if (value === this.value && this.reset && !this.required) {
				this.$emit("input", "");
			}
		},
		onInput(value) {
			this.$emit("input", value);
		},
		onInvalid() {
			this.$emit("invalid", this.$v.$invalid, this.$v);
		},
		select() {
			this.focus();
		}
	},
	validations() {
		return {
			value: {
				required: this.required ? validateRequired : true
			}
		};
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

.k-toggles-input {
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
	background: var(--color-white);
}
.k-toggles-input label {
	align-items: center;
	background: var(--color-white);
	cursor: pointer;
	display: flex;
	font-size: var(--text-sm);
	justify-content: center;
	line-height: 1.25;
	padding: 0 var(--spacing-3);
	height: 100%;
}

.k-toggles-input .k-icon + .k-toggles-text {
	margin-inline-start: var(--spacing-2);
}
.k-toggles-input input:focus:not(:checked) + label {
	background: var(--color-gray-200);
}

.k-toggles-input input:checked + label {
	background: var(--color-black);
	color: var(--color-white);
}
</style>
