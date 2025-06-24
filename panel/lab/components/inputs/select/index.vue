<template>
	<k-lab-form>
		<k-lab-examples class="k-lab-input-examples">
			<k-lab-example label="Default">
				<k-select-input
					name="select"
					:options="options"
					:value="value"
					@input="value = $event"
				/>
			</k-lab-example>

			<k-lab-example label="Autofocus">
				<k-select-input
					:autofocus="true"
					:options="options"
					:value="value"
					@input="value = $event"
				/>
			</k-lab-example>

			<k-lab-example label="Required">
				<k-select-input
					:options="options"
					:required="true"
					:value="value"
					@input="value = $event"
				/>
			</k-lab-example>

			<k-lab-example label="Focus" class="k-lab-input-examples-focus">
				<k-select-input
					ref="input"
					:options="options"
					:value="value"
					@input="value = $event"
				/>
				<k-button text="Focus" variant="filled" @click="$refs.input.focus()" />
			</k-lab-example>

			<k-lab-example label="Disabled">
				<k-select-input
					:disabled="true"
					:options="options"
					:value="value"
					@input="value = $event"
				/>
			</k-lab-example>

			<k-lab-example label="Placeholder">
				<k-select-input
					:options="options"
					:value="value"
					placeholder="Placeholder text…"
					@input="value = $event"
				/>
			</k-lab-example>

			<k-lab-example label="No empty option">
				<k-select-input :required="true" :options="options" value="b" />
			</k-lab-example>

			<k-lab-example label="Multiple: true">
				<k-select-input
					:multiple="true"
					:options="options"
					:value="value"
					@input="value = $event"
				/>
			</k-lab-example>
		</k-lab-examples>
	</k-lab-form>
</template>

<script>
export default {
	data() {
		return {
			value: null
		};
	},
	computed: {
		options() {
			return [
				{ text: "Option A", value: "a" },
				{ text: "Option B", value: "b" },
				{ text: "Option C", value: "c" }
			];
		}
	}
};
</script>
