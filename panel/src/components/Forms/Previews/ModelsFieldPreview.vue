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
			this.tags = [];
			const missing = [];

			// loop through all values…
			for (let index = 0; index < this.value.length; index++) {
				const value = this.value[index];

				if (typeof value === "string") {
					// string = item needs to be fetched from API (add skeleton)
					missing.push(value);
					this.tags.push(this.skeleton(value));
				} else {
					// item object can be added as tag directly
					this.tags.push(this.tag(value));
				}
			}

			// get all missing items from API
			// and replace in tags array
			if (missing.length > 0) {
				const data = await await this.$panel.get(this.$options.endpoint, {
					query: {
						items: missing.join(",")
					}
				});

				for (let index = 0; index < missing.length; index++) {
					if (data.items[index]) {
						const key = this.tags.findIndex((tag) => tag.id === missing[index]);
						this.tags[key] = data.items[index];
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
