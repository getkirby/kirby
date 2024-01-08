<template>
	<k-lab-form>
		<k-lab-examples>
			<k-lab-example label="Default">
				<component
					:is="`k-${type}-inputbox`"
					:name="type"
					:value="input"
					@input="emit"
				/>
			</k-lab-example>
			<k-lab-example label="Autofocus">
				<component
					:is="`k-${type}-inputbox`"
					:autofocus="true"
					:value="input"
					@input="emit"
				/>
			</k-lab-example>
			<k-lab-example label="Required">
				<component
					:is="`k-${type}-inputbox`"
					:required="true"
					:value="input"
					@input="emit"
				/>
			</k-lab-example>
			<k-lab-example v-if="placeholder" label="Placeholder">
				<component
					:is="`k-${type}-inputbox`"
					:value="input"
					placeholder="Placeholder text …"
					@input="emit"
				/>
			</k-lab-example>
			<k-lab-example v-if="description" label="Before & After">
				<component
					:is="`k-${type}-inputbox`"
					:value="input"
					after="After"
					before="Before"
					@input="emit"
				/>
			</k-lab-example>
			<k-lab-example v-if="icon" label="Icon">
				<component
					:is="`k-${type}-inputbox`"
					:value="input"
					icon="edit"
					@input="emit"
				/>
			</k-lab-example>
			<k-lab-example label="Disabled">
				<component
					:is="`k-${type}-inputbox`"
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
		description: {
			default: true,
			type: Boolean
		},
		icon: {
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
			type: [String, Number]
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
