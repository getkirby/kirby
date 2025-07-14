<template>
	<k-field
		v-bind="$props"
		:class="['k-url-field', $attrs.class]"
		:input="id"
		:style="$attrs.style"
	>
		<k-input
			v-bind="$props"
			ref="input"
			type="url"
			@input="$emit('input', $event)"
		>
			<template #icon>
				<k-button
					v-if="link && isValidUrl"
					:icon="icon"
					:link="value"
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
import { props as UrlInput } from "../Input/UrlInput.vue";

/**
 * Have a look at `<k-field>`, `<k-input>` and `<k-url-input>`
 * for additional information.
 * @example <k-url-field :value="url" @input="url = $event" name="url" label="Url" />
 */
export default {
	mixins: [Field, Input, UrlInput],
	inheritAttrs: false,
	props: {
		link: {
			type: Boolean,
			default: true
		},
		icon: {
			type: String,
			default: "url"
		}
	},
	emits: ["input"],
	computed: {
		isValidUrl() {
			return (
				this.value !== "" && this.$helper.url.isUrl(this.value, true) === true
			);
		}
	},
	methods: {
		focus() {
			this.$refs.input.focus();
		}
	}
};
</script>
