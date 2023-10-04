<template>
	<k-ui-form>
		<k-ui-examples>
			<k-ui-example label="Default">
				<component
					:is="`k-${type}-field`"
					:name="type"
					:label="label"
					:options="options"
					:value="input"
					@input="emit"
				/>
			</k-ui-example>
			<k-ui-example label="Autofocus">
				<component
					:is="`k-${type}-field`"
					:autofocus="true"
					:label="label"
					:options="options"
					:value="input"
					@input="emit"
				/>
			</k-ui-example>
			<k-ui-example v-if="info" label="Options with info">
				<component
					:is="`k-${type}-field`"
					:label="label"
					:options="optionsWithInfo"
					:value="input"
					@input="emit"
				/>
			</k-ui-example>
			<k-ui-example label="Required">
				<component
					:is="`k-${type}-field`"
					:label="label"
					:options="optionsWithInfo"
					:required="true"
					:value="input"
					@input="emit"
				/>
			</k-ui-example>
			<k-ui-example v-if="columns" label="Columns">
				<component
					:is="`k-${type}-field`"
					:columns="3"
					:label="label"
					:options="optionsWithInfo"
					:value="input"
					@input="emit"
				/>
			</k-ui-example>
			<k-ui-example label="Disabled">
				<component
					:is="`k-${type}-field`"
					:label="label"
					:options="optionsWithInfo"
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
		columns: {
			default: true,
			type: Boolean,
		},
		info: {
			default: true,
			type: Boolean,
		},
		type: String,
		value: {
			default: null,
			type: [Array, String],
		},
	},
	data() {
		return {
			input: null,
		};
	},
	computed: {
		label() {
			return this.$helper.string.ucfirst(this.type);
		},
		options() {
			return [
				{ text: "Option A", value: "a" },
				{ text: "Option B", value: "b" },
				{ text: "Option C", value: "c" },
			];
		},
		optionsWithInfo() {
			return [
				{ text: "Option A", value: "a", info: "This is some info text" },
				{ text: "Option B", value: "b", info: "This is some info text" },
				{ text: "Option C", value: "c", info: "This is some info text" },
			];
		},
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
