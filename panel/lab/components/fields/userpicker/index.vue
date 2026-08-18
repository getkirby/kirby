<template>
	<k-lab-form>
		<k-lab-examples class="k-lab-field-examples">
			<k-box theme="notice" icon="alert">
				The users field does not work without the backend yet
			</k-box>

			<k-lab-example label="Default">
				<k-userpicker-field
					name="users"
					label="users"
					:value="value"
					@input="value = $event"
				/>
			</k-lab-example>

			<k-lab-example label="Autofocus">
				<k-userpicker-field
					:autofocus="true"
					label="users"
					:value="value"
					@input="value = $event"
				/>
			</k-lab-example>

			<k-lab-example label="Required">
				<k-userpicker-field
					label="users"
					:required="true"
					:value="value"
					@input="value = $event"
				/>
			</k-lab-example>

			<k-lab-example label="Placeholder">
				<k-userpicker-field
					label="users"
					:value="value"
					placeholder="Placeholder text …"
					@input="value = $event"
				/>
			</k-lab-example>

			<k-lab-example label="Help">
				<k-userpicker-field
					label="users"
					:value="value"
					help="This is some help text"
					@input="value = $event"
				/>
			</k-lab-example>

			<k-lab-example label="Before & After">
				<k-userpicker-field
					label="users"
					:value="value"
					after="After"
					before="Before"
					@input="value = $event"
				/>
			</k-lab-example>

			<k-lab-example label="Icon">
				<k-userpicker-field
					label="users"
					:value="value"
					icon="edit"
					@input="value = $event"
				/>
			</k-lab-example>

			<k-lab-example label="Disabled">
				<k-userpicker-field
					:disabled="true"
					label="users"
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
			value: []
		};
	}
};
</script>
