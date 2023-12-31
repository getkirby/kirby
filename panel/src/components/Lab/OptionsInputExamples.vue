<template>
	<k-lab-form>
		<k-lab-examples class="k-lab-options-input-examples">
			<k-lab-example label="Default">
				<component
					:is="`k-${type}-input`"
					:name="type"
					:options="options"
					:value="input"
					@input="emit"
				/>
			</k-lab-example>
			<k-lab-example label="Autofocus">
				<component
					:is="`k-${type}-input`"
					:autofocus="true"
					:options="options"
					:value="input"
					@input="emit"
				/>
			</k-lab-example>
			<k-lab-example label="Required">
				<component
					:is="`k-${type}-input`"
					:options="options"
					:required="true"
					:value="input"
					@input="emit"
				/>
			</k-lab-example>
			<k-lab-example v-if="info" label="Options with info">
				<component
					:is="`k-${type}-input`"
					:options="optionsWithInfo"
					:value="input"
					@input="emit"
				/>
			</k-lab-example>
			<k-lab-example label="Focus">
				<div style="margin-bottom: 1.5rem">
					<component
						:is="`k-${type}-input`"
						ref="input"
						:options="info ? optionsWithInfo : options"
						:value="input"
						style="margin-bottom: 1.5rem"
						@input="emit"
					/>
				</div>
				<k-button variant="filled" size="sm" @click="$refs.input.focus()">
					Focus
				</k-button>
			</k-lab-example>
			<k-lab-example label="Disabled">
				<component
					:is="`k-${type}-input`"
					:disabled="true"
					:options="info ? optionsWithInfo : options"
					:value="input"
					@input="emit"
				/>
			</k-lab-example>
			<slot :options="options" :options-with-info="optionsWithInfo" />
		</k-lab-examples>
	</k-lab-form>
</template>

<script>
export default {
	props: {
		info: {
			default: true,
			type: Boolean
		},
		options: {
			default() {
				return [
					{ text: "Option A", value: "a" },
					{ text: "Option B", value: "b" },
					{ text: "Option C", value: "c" }
				];
			},
			type: Array
		},
		optionsWithInfo: {
			default() {
				return [
					{ text: "Option A", value: "a", info: "This is some info text" },
					{ text: "Option B", value: "b", info: "This is some info text" },
					{ text: "Option C", value: "c", info: "This is some info text" }
				];
			},
			type: Array
		},
		type: String,
		value: [Array, String, Number]
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
.k-lab-options-input-examples fieldset:invalid {
	outline: 2px solid var(--color-red-600);
}
.k-lab-options-input-examples *:not([type="checkbox"], [type="radio"]):invalid {
	outline: 2px solid var(--color-red-600);
}
</style>
