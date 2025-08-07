<template>
	<k-lab-form>
		<k-lab-examples class="k-lab-input-examples">
			<k-lab-example label="Default">
				<k-number-input name="number" :value="value" @input="value = $event" />
			</k-lab-example>

			<k-lab-example label="Autofocus">
				<k-number-input
					:autofocus="true"
					:value="value"
					@input="value = $event"
				/>
			</k-lab-example>

			<k-lab-example label="Required">
				<k-number-input
					:required="true"
					:value="value"
					@input="value = $event"
				/>
			</k-lab-example>

			<k-lab-example label="Placeholder">
				<k-number-input
					placeholder="Placeholder text …"
					:value="value"
					@input="value = $event"
				/>
			</k-lab-example>

			<k-lab-example label="Focus" class="k-lab-input-examples-focus">
				<k-number-input ref="input" :value="value" @input="value = $event" />
				<k-button text="Focus" variant="filled" @click="$refs.input.focus()" />
			</k-lab-example>

			<k-lab-example label="Disabled">
				<k-number-input
					:disabled="true"
					:value="value"
					@input="value = $event"
				/>
			</k-lab-example>

			<k-lab-example label="Step: 0.01">
				<k-number-input :step="0.01" :value="value" @input="value = $event" />
			</k-lab-example>

			<k-lab-example label="Step: any">
				<k-number-input step="any" :value="value" @input="value = $event" />
			</k-lab-example>

			<k-lab-example label="Min: 5">
				<k-number-input :min="5" :value="value" @input="value = $event" />
			</k-lab-example>

			<k-lab-example label="Max: 10">
				<k-number-input :max="10" :value="value" @input="value = $event" />
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
	}
};
</script>
