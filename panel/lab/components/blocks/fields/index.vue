<template>
	<k-lab-examples>
		<k-lab-example label="Default">
			<k-block
				:content="content"
				:fieldset="fieldset"
				type="fields"
				@update="update"
			/>
		</k-lab-example>
		<k-lab-example label="Tabs">
			<k-block
				:content="content"
				:fieldset="fieldsetWithTabs"
				type="fields"
				@update="update"
			/>
		</k-lab-example>
		<k-lab-example label="Selected">
			<k-block
				:content="content"
				:fieldset="fieldsetWithTabs"
				:is-editable="true"
				:is-last-selected="true"
				:is-selected="true"
				type="fields"
				@update="update"
			/>
		</k-lab-example>
		<k-lab-example label="Disabled">
			<k-block
				:content="content"
				:disabled="true"
				:fieldset="fieldsetWithTabs"
				type="fields"
				@update="update"
			/>
		</k-lab-example>
		<k-lab-example label="Output">
			<k-code>{{ content }}</k-code>
		</k-lab-example>
	</k-lab-examples>
</template>

<script>
export default {
	data() {
		return {
			content: {
				name: null,
				email: null,
				toggle: false
			}
		};
	},
	computed: {
		contentTab() {
			return {
				name: "content",
				label: "Content",
				fields: {
					name: {
						label: "Name",
						type: "text",
						width: "1/2"
					},
					email: {
						label: "Email",
						type: "email",
						width: "1/2"
					}
				}
			};
		},
		fieldset() {
			return {
				name: "Test",
				wysiwyg: true,
				tabs: {
					content: this.contentTab
				}
			};
		},
		fieldsetWithTabs() {
			return {
				...this.fieldset,
				tabs: {
					content: this.contentTab,
					settings: this.settingsTab
				}
			};
		},
		settingsTab() {
			return {
				name: "settings",
				label: "Settings",
				fields: {
					toggle: {
						label: "Setting",
						type: "toggle"
					}
				}
			};
		}
	}
};
</script>
