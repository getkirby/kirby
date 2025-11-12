<template>
	<k-field
		v-bind="$props"
		:class="['k-checkboxes-field', $attrs.class]"
		:counter="counterOptions"
		:input="id + '-0'"
		:style="$attrs.style"
	>
		<template #counter>
			<k-counter
				v-if="counterOptions"
				v-bind="counterOptions"
				:required="required"
				class="k-field-counter"
			/>

			<label v-if="batch">
				<input ref="batch" type="checkbox" @input="batchSelect" />
				<span class="sr-only">select/deselect all</span>
			</label>
		</template>

		<k-empty
			v-if="!options?.length"
			:text="$t('options.none')"
			icon="checklist"
		/>
		<k-checkboxes-input
			v-else
			ref="input"
			v-bind="$props"
			@input="$emit('input', $event)"
		/>
	</k-field>
</template>

<script>
import { props as Field } from "../Field.vue";
import { props as Input } from "../Input.vue";
import { props as CheckboxesInput } from "../Input/CheckboxesInput.vue";
import counter from "@/mixins/forms/counter.js";

/**
 * Have a look at `<k-field>`, `<k-input>` and `<k-checkboxes-input>` for additional information.
 */
export default {
	mixins: [Field, Input, CheckboxesInput, counter],
	inheritAttrs: false,
	props: {
		batch: Boolean
	},
	watch: {
		value() {
			this.checkBatchToggleState();
		}
	},
	mounted() {
		this.checkBatchToggleState();
	},
	methods: {
		batchSelect(e) {
			if (e.target.checked) {
				this.$refs.input.selectAll();
			} else {
				this.$refs.input.deselectAll();
			}
		},
		checkBatchToggleState() {
			// indeterminate state
			this.$refs.batch.indeterminate =
				this.value.length > 0 && this.value.length !== this.options.length;

			// checked state
			this.$refs.batch.checked = this.value.length === this.options.length;
		},
		focus() {
			this.$refs.input.focus();
		}
	}
};
</script>
