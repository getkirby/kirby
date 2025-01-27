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
		<k-lab-example label="Lots of tabs">
			<k-block
				:content="content"
				:fieldset="fieldsetWithLotsOfTabs"
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
		fieldsetWithLotsOfTabs() {
			return {
				...this.fieldset,
				name: "This is a quite long name that might wrap",
				label: "This is a quite long label that might wrap",
				tabs: {
					content: {
						name: "content",
						label: "Content",
						fields: {}
					},
					settings: {
						name: "settings",
						label: "Settings",
						fields: {}
					},
					seo: {
						name: "seo",
						label: "SEO",
						fields: {}
					},
					validation: {
						name: "validation",
						label: "Validation",
						fields: {}
					},
					help: {
						name: "help",
						label: "Help",
						fields: {}
					},
					theme: {
						name: "theme",
						label: "Theme",
						fields: {}
					},
					misc: {
						name: "misc",
						label: "Misc",
						fields: {}
					}
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
	},
	methods: {
		update(content) {
			this.content = {
				...this.content,
				...content
			};
		}
	}
};
</script>
