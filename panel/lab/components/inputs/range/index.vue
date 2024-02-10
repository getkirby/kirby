<template>
	<k-lab-form>
		<k-lab-examples class="k-lab-input-examples">
			<k-lab-example label="Default">
				<k-range-input name="range" :value="value" @input="value = $event" />
			</k-lab-example>

			<k-lab-example label="Autofocus">
				<k-range-input
					:autofocus="true"
					:value="value"
					@input="value = $event"
				/>
			</k-lab-example>

			<k-lab-example label="Required">
				<k-range-input
					:required="true"
					:value="value"
					@input="value = $event"
				/>
			</k-lab-example>

			<k-lab-example label="Focus" class="k-lab-input-examples-focus">
				<k-range-input ref="input" :value="value" @input="value = $event" />
				<k-button text="Focus" variant="filled" @click="$refs.input.focus()" />
			</k-lab-example>

			<k-lab-example label="Disabled">
				<k-range-input
					:disabled="true"
					:value="value"
					@input="value = $event"
				/>
			</k-lab-example>

			<k-lab-example label="Step: 0.01">
				<k-range-input :step="0.01" :value="value" @input="value = $event" />
			</k-lab-example>

			<k-lab-example label="Min: 5">
				<k-range-input :min="5" :value="value" @input="value = $event" />
			</k-lab-example>

			<k-lab-example label="Max: 10">
				<k-range-input :max="10" :value="value" @input="value = $event" />
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
