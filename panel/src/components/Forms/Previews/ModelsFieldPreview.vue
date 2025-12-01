<template>
	<k-tags-field-preview
		:html="html"
		:value="tags"
		class="k-models-field-preview"
	/>
</template>

<script>
import FieldPreview from "@/mixins/forms/fieldPreview.js";

export default {
	mixins: [FieldPreview],
	props: {
		html: {
			type: Boolean,
			default: true
		},
		value: {
			default: () => [],
			type: [Array, String]
		}
	},
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
				const data = await await this.$panel.get(this.$options.endpoint, {
					query: {
						items: missing.join(",")
					}
				});

				for (let index = 0; index < missing.length; index++) {
					const id = missing[index];
					const tag = data.items[index];
					const key = this.tags.findIndex((tag) => tag.id === id);
					this.tags[key] = { ...tag, id };
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
