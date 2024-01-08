<template>
	<k-lab-form>
		<k-lab-examples>
			<k-lab-example label="Default">
				<component
					:is="`k-${type}-inputbox`"
					:name="type"
					:options="options"
					:value="input"
					@input="emit"
				/>
			</k-lab-example>
			<k-lab-example label="Autofocus">
				<component
					:is="`k-${type}-inputbox`"
					:autofocus="true"
					:options="options"
					:value="input"
					@input="emit"
				/>
			</k-lab-example>
			<k-lab-example v-if="info" label="Options with info">
				<component
					:is="`k-${type}-inputbox`"
					:options="optionsWithInfo"
					:value="input"
					@input="emit"
				/>
			</k-lab-example>
			<k-lab-example label="Required">
				<component
					:is="`k-${type}-inputbox`"
					:options="optionsWithInfo"
					:required="true"
					:value="input"
					@input="emit"
				/>
			</k-lab-example>
			<k-lab-example v-if="placeholder" label="Placeholder">
				<component
					:is="`k-${type}-inputbox`"
					:options="options"
					:value="input"
					placeholder="Placeholder text …"
					@input="emit"
				/>
			</k-lab-example>
			<k-lab-example v-if="description" label="Before & After">
				<component
					:is="`k-${type}-inputbox`"
					:options="options"
					:value="input"
					after="After"
					before="Before"
					@input="emit"
				/>
			</k-lab-example>
			<k-lab-example v-if="icon" label="Icon">
				<component
					:is="`k-${type}-inputbox`"
					:options="options"
					:value="input"
					icon="edit"
					@input="emit"
				/>
			</k-lab-example>
			<k-lab-example v-if="columns" label="Columns">
				<component
					:is="`k-${type}-inputbox`"
					:columns="3"
					:options="optionsWithInfo"
					:value="input"
					@input="emit"
				/>
			</k-lab-example>
			<k-lab-example label="Disabled">
				<component
					:is="`k-${type}-inputbox`"
					:options="optionsWithInfo"
					:disabled="true"
					:value="input"
					@input="emit"
				/>
			</k-lab-example>
			<k-lab-example label="No options">
				<component :is="`k-${type}-inputbox`" />
			</k-lab-example>
			<slot :options="options" :options-with-info="optionsWithInfo" />
		</k-lab-examples>
	</k-lab-form>
</template>

<script>
export default {
	props: {
		columns: {
			default: true,
			type: Boolean
		},
		description: {
			default: true,
			type: Boolean
		},
		icon: {
			default: true,
			type: Boolean
		},
		info: {
			default: true,
			type: Boolean
		},
		placeholder: {
			default: true,
			type: Boolean
		},
		type: String,
		value: {
			default: null,
			type: [Array, String]
		}
	},
	emits: ["input"],
	data() {
		return {
			input: null
		};
	},
	computed: {
		options() {
			return [
				{ text: "Option A", value: "a" },
				{ text: "Option B", value: "b" },
				{ text: "Option C", value: "c" }
			];
		},
		optionsWithInfo() {
			return [
				{ text: "Option A", value: "a", info: "This is some info text" },
				{ text: "Option B", value: "b", info: "This is some info text" },
				{ text: "Option C", value: "c", info: "This is some info text" }
			];
		}
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
