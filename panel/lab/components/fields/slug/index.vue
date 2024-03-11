<template>
	<k-lab-form>
		<k-lab-examples class="k-lab-field-examples">
			<k-lab-example label="Default">
				<k-slug-field
					name="slug"
					label="slug"
					:value="value"
					@input="value = $event"
				/>
			</k-lab-example>

			<k-lab-example label="Autofocus">
				<k-slug-field
					:autofocus="true"
					label="slug"
					:value="value"
					@input="value = $event"
				/>
			</k-lab-example>

			<k-lab-example label="Required">
				<k-slug-field
					label="slug"
					:required="true"
					:value="value"
					@input="value = $event"
				/>
			</k-lab-example>

			<k-lab-example label="Placeholder">
				<k-slug-field
					label="slug"
					:value="value"
					placeholder="Placeholder text …"
					@input="value = $event"
				/>
			</k-lab-example>

			<k-lab-example label="Help">
				<k-slug-field
					label="slug"
					:value="value"
					help="This is some help text"
					@input="value = $event"
				/>
			</k-lab-example>

			<k-lab-example label="Before & After">
				<k-slug-field
					label="slug"
					:value="value"
					after="After"
					before="Before"
					@input="value = $event"
				/>
			</k-lab-example>

			<k-lab-example label="Icon">
				<k-slug-field
					label="slug"
					:value="value"
					icon="edit"
					@input="value = $event"
				/>
			</k-lab-example>

			<k-lab-example label="Disabled">
				<k-slug-field
					:disabled="true"
					label="slug"
					:value="value"
					@input="value = $event"
				/>
			</k-lab-example>

			<k-lab-example label="Wizard">
				<k-slug-field
					:form-data="{
						title: 'This is a custom slug'
					}"
					:value="value"
					:wizard="{
						text: 'Create slug',
						field: 'title'
					}"
					label="Slug"
					@input="value = $event"
				/>

				<k-code style="margin-top: 1.5rem"
					>{ title: 'This is a custom slug' }</k-code
				>
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
