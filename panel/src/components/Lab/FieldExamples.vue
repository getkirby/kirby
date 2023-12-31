<template>
	<k-lab-form>
		<k-lab-examples>
			<k-lab-example label="Default">
				<component
					:is="`k-${type}-field`"
					:name="type"
					:label="label"
					:value="input"
					@input="emit"
				/>
			</k-lab-example>
			<k-lab-example label="Autofocus">
				<component
					:is="`k-${type}-field`"
					:autofocus="true"
					:label="label"
					:value="input"
					@input="emit"
				/>
			</k-lab-example>
			<k-lab-example label="Required">
				<component
					:is="`k-${type}-field`"
					:label="label"
					:required="true"
					:value="input"
					@input="emit"
				/>
			</k-lab-example>
			<k-lab-example v-if="placeholder" label="Placeholder">
				<component
					:is="`k-${type}-field`"
					:label="label"
					:value="input"
					placeholder="Placeholder text …"
					@input="emit"
				/>
			</k-lab-example>
			<k-lab-example label="Help">
				<component
					:is="`k-${type}-field`"
					:label="label"
					:value="input"
					help="This is some help text"
					@input="emit"
				/>
			</k-lab-example>
			<k-lab-example v-if="description" label="Before & After">
				<component
					:is="`k-${type}-field`"
					:label="label"
					:value="input"
					after="After"
					before="Before"
					@input="emit"
				/>
			</k-lab-example>
			<k-lab-example v-if="icon" label="Icon">
				<component
					:is="`k-${type}-field`"
					:label="label"
					:value="input"
					icon="edit"
					@input="emit"
				/>
			</k-lab-example>
			<k-lab-example label="Disabled">
				<component
					:is="`k-${type}-field`"
					:disabled="true"
					:label="label"
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
			type: [String, Number, Array]
		}
	},
	emits: ["input"],
	data() {
		return {
			input: null
		};
	},
	computed: {
		label() {
			return this.$helper.string.ucfirst(this.type);
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
