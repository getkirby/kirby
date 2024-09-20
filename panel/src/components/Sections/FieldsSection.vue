<template>
	<k-section
		v-if="!isLoading"
		:class="['k-fields-section', $attrs.class]"
		:headline="issue ? $t('error') : null"
		:style="$attrs.style"
	>
		<k-box
			v-if="issue"
			:text="issue.message"
			:html="false"
			icon="alert"
			theme="negative"
		/>
		<k-form
			:fields="fields"
			:validate="true"
			:value="content"
			:disabled="lock && lock.state === 'lock'"
			@input="$emit('input', $event)"
			@submit="$emit('submit', $event)"
		/>
	</k-section>
</template>

<script>
import SectionMixin from "@/mixins/section.js";

export default {
	mixins: [SectionMixin],
	inheritAttrs: false,
	props: {
		content: Object
	},
	emits: ["input", "submit"],
	data() {
		return {
			fields: {},
			isLoading: true,
			issue: null
		};
	},
	watch: {
		// Reload values and field definitions
		// when the view has changed in the backend
		timestamp() {
			this.fetch();
		}
	},
	mounted() {
		this.fetch();
	},
	methods: {
		async fetch() {
			try {
				const response = await this.load();
				this.fields = response.fields;

				for (const name in this.fields) {
					this.fields[name].section = this.name;
					this.fields[name].endpoints = {
						field: this.parent + "/fields/" + name,
						section: this.parent + "/sections/" + this.name,
						model: this.parent
					};
				}
			} catch (error) {
				this.issue = error;
			} finally {
				this.isLoading = false;
			}
		}
	}
};
</script>

<style>
.k-fields-section input[type="submit"] {
	display: none;
}

[data-locked="true"] .k-fields-section {
	opacity: 0.2;
	pointer-events: none;
}
</style>
