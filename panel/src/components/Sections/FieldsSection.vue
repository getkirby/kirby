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
			:value="values"
			:disabled="lock && lock.state === 'lock'"
			@input="onInput"
			@submit="onSubmit"
		/>
	</k-section>
</template>

<script>
import SectionMixin from "@/mixins/section.js";
import debounce from "@/helpers/debounce.js";

export default {
	mixins: [SectionMixin],
	inheritAttrs: false,
	data() {
		return {
			fields: {},
			isLoading: true,
			issue: null
		};
	},
	computed: {
		values() {
			return this.$store.getters["content/values"]();
		}
	},
	watch: {
		// Reload values and field definitions
		// when the view has changed in the backend
		timestamp() {
			this.fetch();
		}
	},
	mounted() {
		this.onInput = debounce(this.onInput, 50);
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
		},
		onInput(values, field, fieldName) {
			this.$store.dispatch("content/update", [fieldName, values[fieldName]]);
		},
		onSubmit(values) {
			// ensure that all values are actually committed to content store
			this.$store.dispatch("content/update", [null, values]);
			this.$events.emit("keydown.cmd.s", values);
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
