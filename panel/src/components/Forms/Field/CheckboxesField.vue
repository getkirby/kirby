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

			<k-button-group v-if="batch" layout="collapsed">
				<k-button
					:disabled="value.length === 0"
					:responsive="true"
					icon="deselect-all"
					size="xs"
					variant="filled"
					@click="deselectAll"
				>
					{{ $t("deselect.all") }}
				</k-button>
				<k-button
					:disabled="value.length >= options.length"
					:responsive="true"
					icon="select-all"
					size="xs"
					variant="filled"
					@click="selectAll"
				>
					{{ $t("select.all") }}
				</k-button>
			</k-button-group>
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
	emits: ["input"],
	methods: {
		deselectAll() {
			this.$refs.input.deselectAll();
		},
		selectAll() {
			this.$refs.input.selectAll();
		},
		focus() {
			this.$refs.input.focus();
		}
	}
};
</script>
