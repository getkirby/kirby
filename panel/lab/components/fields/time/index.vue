<template>
	<k-lab-form>
		<k-lab-examples class="k-lab-field-examples">
			<k-lab-example label="Default">
				<k-time-field
					name="time"
					label="time"
					:value="value"
					@input="value = $event"
				/>
			</k-lab-example>

			<k-lab-example label="Autofocus">
				<k-time-field
					:autofocus="true"
					label="time"
					:value="value"
					@input="value = $event"
				/>
			</k-lab-example>

			<k-lab-example label="Required">
				<k-time-field
					label="time"
					:required="true"
					:value="value"
					@input="value = $event"
				/>
			</k-lab-example>

			<k-lab-example label="Placeholder">
				<k-time-field
					label="time"
					:value="value"
					placeholder="Placeholder text …"
					@input="value = $event"
				/>
			</k-lab-example>

			<k-lab-example label="Help">
				<k-time-field
					label="time"
					:value="value"
					help="This is some help text"
					@input="value = $event"
				/>
			</k-lab-example>

			<k-lab-example label="Before & After">
				<k-time-field
					label="time"
					:value="value"
					after="After"
					before="Before"
					@input="value = $event"
				/>
			</k-lab-example>

			<k-lab-example label="Icon">
				<k-time-field
					label="time"
					:value="value"
					icon="edit"
					@input="value = $event"
				/>
			</k-lab-example>

			<k-lab-example label="Disabled">
				<k-time-field
					:disabled="true"
					label="time"
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
