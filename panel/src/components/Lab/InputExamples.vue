<template>
	<k-lab-form>
		<k-lab-examples class="k-lab-input-examples">
			<k-lab-example label="Default">
				<component
					:is="`k-${type}-input`"
					:name="type"
					:value="input"
					@input="emit"
				/>
			</k-lab-example>
			<k-lab-example label="Autofocus">
				<component
					:is="`k-${type}-input`"
					:autofocus="true"
					:value="input"
					@input="emit"
				/>
			</k-lab-example>
			<k-lab-example label="Required">
				<component
					:is="`k-${type}-input`"
					:required="true"
					:value="input"
					@input="emit"
				/>
			</k-lab-example>
			<k-lab-example v-if="placeholder" label="Placeholder">
				<component
					:is="`k-${type}-input`"
					:value="input"
					placeholder="Placeholder text …"
					@input="emit"
				/>
			</k-lab-example>
			<k-lab-example label="Focus">
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
			</k-lab-example>
			<k-lab-example label="Disabled">
				<component
					:is="`k-${type}-input`"
					:disabled="true"
					:value="input"
					@input="emit"
				/>
			</k-lab-example>
			<slot />
		</k-lab-examples>
	</k-lab-form>
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
	emits: ["input"],
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
.k-lab-input-examples *:not([type="checkbox"], [type="radio"]):invalid {
	outline: 2px solid var(--color-red-600) !important;
}
</style>
