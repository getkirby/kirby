<template>
	<k-field
		v-bind="$props"
		:class="['k-email-field', $attrs.class]"
		:input="id"
		:style="$attrs.style"
	>
		<k-input
			v-bind="$props"
			ref="input"
			type="email"
			@input="$emit('input', $event)"
		>
			<template #icon>
				<k-button
					v-if="link"
					:icon="icon"
					:link="mailto"
					:title="$t('open')"
					class="k-input-icon-button"
					tabindex="-1"
					target="_blank"
				/>
			</template>
		</k-input>
	</k-field>
</template>

<script>
import { props as Field } from "../Field.vue";
import { props as Input } from "../Input.vue";
import { props as EmailInput } from "../Input/EmailInput.vue";

/**
 * Have a look at `<k-field>`, `<k-input>` and `<k-email-input>` for additional information.
 * @example <k-email-field :value="email" @input="email = $event" name="email" label="Email" />
 */
export default {
	mixins: [Field, Input, EmailInput],
	inheritAttrs: false,
	props: {
		link: {
			type: Boolean,
			default: true
		},
		icon: {
			type: String,
			default: "email"
		}
	},
	emits: ["input"],
	computed: {
		mailto() {
			return this.value?.length > 0 ? "mailto:" + this.value : null;
		}
	},
	methods: {
		focus() {
			this.$refs.input.focus();
		}
	}
};
</script>
