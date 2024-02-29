<template>
	<k-lab-form>
		<k-lab-examples class="k-lab-input-examples">
			<k-lab-example label="Default">
				<k-calendar-input
					name="calendar"
					:value="value"
					@input="value = $event"
				/>
			</k-lab-example>

			<k-lab-example label="Autofocus">
				<k-calendar-input
					:autofocus="true"
					:value="value"
					@input="value = $event"
				/>
			</k-lab-example>

			<k-lab-example label="Required">
				<k-calendar-input
					:required="true"
					:value="value"
					@input="value = $event"
				/>
			</k-lab-example>

			<k-lab-example label="Focus" class="k-lab-input-examples-focus">
				<k-calendar-input ref="input" :value="value" @input="value = $event" />
				<k-button text="Focus" variant="filled" @click="$refs.input.focus()" />
			</k-lab-example>

			<k-lab-example label="Disabled">
				<k-calendar-input
					:disabled="true"
					:value="value"
					@input="value = $event"
				/>
			</k-lab-example>

			<k-lab-example label="Min (2023-02-15)">
				<k-calendar-input
					min="2023-02-15"
					:value="value"
					@input="value = $event"
				/>
			</k-lab-example>

			<k-lab-example label="Max (2023-09-15)">
				<k-calendar-input
					max="2023-09-15"
					:value="value"
					@input="value = $event"
				/>
			</k-lab-example>

			<k-lab-example label="Min (2023-09-02) Max (2023-09-15)">
				<k-calendar-input
					min="2023-09-02"
					max="2023-09-15"
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
