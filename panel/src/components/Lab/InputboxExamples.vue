<template>
	<k-ui-form>
		<k-ui-examples>
			<k-ui-example label="Default">
				<component
					:is="`k-${type}-inputbox`"
					:name="type"
					:value="input"
					@input="emit"
				/>
			</k-ui-example>
			<k-ui-example label="Autofocus">
				<component
					:is="`k-${type}-inputbox`"
					:autofocus="true"
					:value="input"
					@input="emit"
				/>
			</k-ui-example>
			<k-ui-example label="Required">
				<component
					:is="`k-${type}-inputbox`"
					:required="true"
					:value="input"
					@input="emit"
				/>
			</k-ui-example>
			<k-ui-example v-if="placeholder" label="Placeholder">
				<component
					:is="`k-${type}-inputbox`"
					:value="input"
					placeholder="Placeholder text …"
					@input="emit"
				/>
			</k-ui-example>
			<k-ui-example v-if="description" label="Before & After">
				<component
					:is="`k-${type}-inputbox`"
					:value="input"
					after="After"
					before="Before"
					@input="emit"
				/>
			</k-ui-example>
			<k-ui-example v-if="icon" label="Icon">
				<component
					:is="`k-${type}-inputbox`"
					:value="input"
					icon="edit"
					@input="emit"
				/>
			</k-ui-example>
			<k-ui-example label="Disabled">
				<component
					:is="`k-${type}-inputbox`"
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
		description: {
			default: true,
			type: Boolean,
		},
		icon: {
			default: true,
			type: Boolean,
		},
		placeholder: {
			default: true,
			type: Boolean,
		},
		type: String,
		value: {
			default: null,
			type: [String, Number],
		},
	},
	data() {
		return {
			input: null,
		};
	},
	watch: {
		value: {
			handler(value) {
				this.input = value;
			},
			immediate: true,
		},
	},
	methods: {
		emit(input) {
			this.input = input;
			this.$emit("input", input);
		},
	},
};
</script>
