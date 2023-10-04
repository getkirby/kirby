<template>
	<k-ui-form>
		<k-ui-examples class="k-ui-input-examples">
			<k-ui-example label="Default">
				<component
					:is="`k-${type}-input`"
					:name="type"
					:value="input"
					@input="emit"
				/>
			</k-ui-example>
			<k-ui-example label="Autofocus">
				<component
					:is="`k-${type}-input`"
					:autofocus="true"
					:value="input"
					@input="emit"
				/>
			</k-ui-example>
			<k-ui-example label="Required">
				<component
					:is="`k-${type}-input`"
					:required="true"
					:value="input"
					@input="emit"
				/>
			</k-ui-example>
			<k-ui-example v-if="placeholder" label="Placeholder">
				<component
					:is="`k-${type}-input`"
					:value="input"
					placeholder="Placeholder text …"
					@input="emit"
				/>
			</k-ui-example>
			<k-ui-example label="Focus">
				<component
					:is="`k-${type}-input`"
					ref="input"
					:value="input"
					style="margin-bottom: 1.5rem"
					@input="emit"
				/>
				<k-button variant="filled" size="sm" @click="$refs.input.focus()">
					Focus
				</k-button>
			</k-ui-example>
			<k-ui-example label="Disabled">
				<component
					:is="`k-${type}-input`"
					:disabled="true"
					:value="input"
					@input="emit"
				/>
			</k-ui-example>
			<slot />
		</k-ui-examples>
	</k-ui-form>
</template>

<script>
export default {
	props: {
		placeholder: {
			default: true,
			type: Boolean
		},
		type: String,
		value: {
			default: null,
			type: [String, Number, Boolean, Object, Array]
		}
	},
	data() {
		return {
			input: null
		};
	},
	watch: {
		value: {
			handler(value) {
				this.input = value;
			},
			immediate: true
		}
	},
	methods: {
		emit(input) {
			this.input = input;
			this.$emit("input", input);
		}
	}
};
</script>

<style>
.k-ui-input-examples *:not([type="checkbox"], [type="radio"]):invalid {
	outline: 2px solid var(--color-red-600) !important;
}
</style>
