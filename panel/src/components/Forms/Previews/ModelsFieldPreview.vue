<template>
	<k-tags-field-preview
		:removable="removable"
		:value="tags"
		class="k-models-field-preview"
		@remove="$emit('remove', $event)"
	/>
</template>

<script>
import FieldPreview from "@/mixins/forms/fieldPreview.js";

/**
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 */
export default {
	mixins: [FieldPreview],
	props: {
		/**
		 * @deprecated 6.0.0 Model items already carry trusted HTML
		 */
		html: {
			type: Boolean
		},
		removable: Boolean,
		value: {
			default: () => [],
			type: [Array, String]
		}
	},
	emits: ["remove"],
	data() {
		return {
			tags: []
		};
	},
	watch: {
		value: {
			immediate: true,
			handler() {
				this.collect();
			}
		}
	},
	created() {
		if (this.html === true) {
			window.panel.deprecated(
				"`k-models-field-preview`: the `html` prop has been deprecated. Model items already carry trusted HTML."
			);
		}
	},
	methods: {
		async collect() {
			let tags = this.$helper.clone(this.tags);
			const missing = [];

			// loop through all values…
			for (let index = 0; index < this.value.length; index++) {
				const value = this.value[index];

				// item object can be added as tag directly
				if (typeof value !== "string") {
					tags.splice(index, 1, this.tag(value));
					continue;
				}

				// no need to reload items that we already have
				const existing = this.tags.find((tag) => tag.id === value);
				if (existing) {
					tags.splice(index, 1, existing);
					continue;
				}

				// item needs to be fetched from API (add skeleton)
				missing.push(value);
				tags.splice(index, 1, this.skeleton(value));
			}

			// replace new temporary tags at once to reduce flickering
			this.tags = tags.slice(0, this.value.length);

			// get all missing items from API
			// and replace in tags array
			if (missing.length > 0) {
				const loaded = await this.$helper.items(
					this.$options.endpoint,
					missing
				);

				for (const [index, id] of missing.entries()) {
					const key = this.tags.findIndex((tag) => tag.id === id);

					// the value might have changed while the request was in flight
					if (key !== -1) {
						this.tags[key] = { ...loaded[index], id };
					}
				}
			}
		},
		skeleton(id) {
			return {
				id,
				image: { icon: "loader", color: "var(--tag-color-disabled-text)" }
			};
		},
		tag(item) {
			return item;
		}
	}
};
</script>
