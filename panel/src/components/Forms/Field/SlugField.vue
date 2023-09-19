<template>
	<k-field v-bind="$props" :input="_uid" :help="preview" class="k-slug-field">
		<template v-if="wizard && wizard.text" #options>
			<k-button
				:text="wizard.text"
				icon="sparkling"
				size="xs"
				variant="filled"
				@click="onWizard"
			/>
		</template>
		<k-slug-inputbox
			ref="input"
			:id="_uid"
			v-bind="$props"
			:value="slug"
			@input="$emit('input', $event)"
		/>
	</k-field>
</template>

<script>
import { props as FieldProps } from "../Field.vue";
import { props as InputboxProps } from "../Inputbox/Types/SlugInputbox.vue";

/**
 * Have a look at `<k-field>` and `<k-slug-inputbox>` for additional information.
 *
 * @example <k-slug-field :value="value" label="Slug" @input="value = $event" />
 * @public
 */
export default {
	mixins: [FieldProps, InputboxProps],
	inheritAttrs: false,
	props: {
		path: {
			type: String
		},
		wizard: {
			type: [Boolean, Object],
			default: false
		}
	},
	data() {
		return {
			slug: null
		};
	},
	computed: {
		preview() {
			if (this.help !== undefined) {
				return this.help;
			}

			if (this.path !== undefined) {
				return this.path + this.slug;
			}

			return null;
		}
	},
	watch: {
		value: {
			handler(value) {
				this.slug = value;
			},
			immediate: true
		}
	},
	methods: {
		onWizard() {
			if (this.formData[this.wizard?.field]) {
				this.slug = this.formData[this.wizard.field];
			}
		}
	}
};
</script>
