<template>
	<k-lab-form>
		<k-lab-examples class="k-lab-input-examples">
			<k-lab-example label="Default">
				<k-alpha-input name="alpha" :value="value" @input="value = $event" />
			</k-lab-example>

			<k-lab-example label="Autofocus">
				<k-alpha-input
					:autofocus="true"
					:value="value"
					@input="value = $event"
				/>
			</k-lab-example>

			<k-lab-example label="Required">
				<k-alpha-input
					:required="true"
					:value="value"
					@input="value = $event"
				/>
			</k-lab-example>

			<k-lab-example label="Focus" class="k-lab-input-examples-focus">
				<k-alpha-input ref="input" :value="value" @input="value = $event" />
				<k-button text="Focus" variant="filled" @click="$refs.input.focus()" />
			</k-lab-example>

			<k-lab-example label="Disabled">
				<k-alpha-input
					:disabled="true"
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
	}
};
</script>
