<template>
	<k-section
		:class="['k-fields-section', $attrs.class]"
		:headline="error ? $t('error') : null"
		:style="$attrs.style"
	>
		<k-box
			v-if="error"
			:text="error"
			:html="false"
			icon="alert"
			theme="negative"
		/>
		<k-form
			v-else
			:fields="fieldsWithAdditionalData"
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

/**
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 */
export default {
	mixins: [SectionMixin],
	inheritAttrs: false,
	props: {
		content: Object,
		error: String,
		fields: {
			type: Object,
			default: () => ({})
		}
	},
	emits: ["input", "submit"],
	computed: {
		fieldsWithAdditionalData() {
			const fields = {};
			const diff = this.$panel.content.diff();

			for (const name in this.fields) {
				fields[name] = {
					...this.fields[name],
					endpoints: {
						field: this.parent + "/fields/" + name,
						section: this.parent + "/sections/" + this.name,
						model: this.parent
					},
					hasDiff: Object.hasOwn(diff, name),
					section: this.name
				};
			}

			return fields;
		}
	},
	async mounted() {
		await this.$nextTick();
		this.$events.emit("section.loaded", this);
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
