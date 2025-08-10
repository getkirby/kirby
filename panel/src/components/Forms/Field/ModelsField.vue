<template>
	<k-field
		v-bind="$props"
		:class="['k-models-field', `k-${$options.type}-field`, $attrs.class]"
		:input="false"
		:style="$attrs.style"
	>
		<template v-if="!disabled" #options>
			<k-button-group
				ref="buttons"
				:buttons="buttons"
				layout="collapsed"
				size="xs"
				variant="filled"
				class="k-field-options"
			/>
		</template>

		<k-dropzone :disabled="!hasDropzone" @drop="drop">
			<k-input-validator
				v-bind="{ min, max, required }"
				:value="JSON.stringify(value)"
			>
				<k-collection
					v-bind="collection"
					v-on="!disabled ? { empty: open } : {}"
					@sort="$emit('input', $event)"
					@sort-change="$emit('change', $event)"
				>
					<template v-if="!disabled" #options="{ index }">
						<k-button
							:title="$t('remove')"
							icon="remove"
							@click="remove(index)"
						/>
					</template>
				</k-collection>
			</k-input-validator>
		</k-dropzone>
	</k-field>
</template>

<script>
import { props as FieldProps } from "@/components/Forms/Field.vue";
import { autofocus, layout } from "@/mixins/props.js";

export default {
	type: "model",
	mixins: [FieldProps, autofocus, layout],
	inheritAttrs: false,
	props: {
		empty: String,
		info: String,
		link: Boolean,
		max: Number,
		min: Number,
		/**
		 * If false, only a single item can be selected
		 */
		multiple: Boolean,
		parent: String,
		search: Boolean,
		size: String,
		text: String,
		value: {
			type: Array,
			default: () => []
		}
	},
	emits: ["change", "input"],
	data() {
		return {
			selected: []
		};
	},
	computed: {
		buttons() {
			return [
				{
					autofocus: this.autofocus,
					text: this.$t("select"),
					icon: "checklist",
					responsive: true,
					click: () => this.open()
				}
			];
		},
		collection() {
			return {
				empty: this.emptyProps,
				items: this.selected,
				layout: this.layout,
				link: this.link,
				size: this.size,
				sortable: !this.disabled && this.selected?.length > 1,
				theme: this.disabled ? "disabled" : null
			};
		},
		hasDropzone() {
			return false;
		},
		more() {
			return !this.max || this.max > this.selected?.length;
		}
	},
	watch: {
		value() {
			this.fetch();
		}
	},
	mounted() {
		this.fetch();
	},
	methods: {
		async fetch() {
			const items = [];
			const missing = [];

			// Loop through IDs to find out
			// which new items we need to fetch data for
			for (const id of this.value) {
				const item = this.selected.find((item) => item.id === id);

				if (item) {
					// If we already have the item, add it to the list to recycle
					items.push(item);
				} else {
					// If we don't have the item, add it to the list to fetch
					// and add a placeholder item to the list (with the same ID
					// so we can later replace it with the actual item)
					missing.push(id);
					items.push({
						id,
						theme: "skeleton",
						image: {
							icon: "loader"
						}
					});
				}
			}

			// Replace the items with recycled items and placeholders
			this.selected = items;

			// If we have any missing items, fetch them
			if (missing.length) {
				const newItems = await this.$panel.api.get(
					this.endpoints.field + "/items",
					{ items: missing }
				);

				// Combine existing and new items in the correct order
				this.selected = this.selected.map(
					(item) => newItems.find((newItem) => newItem.id === item.id) ?? item
				);
			}
		},
		drop() {},
		focus() {},
		open() {
			if (this.disabled) {
				return false;
			}

			this.$panel.dialog.open(this.endpoints.field + "/picker", {
				query: {
					value: this.value
				},
				on: {
					submit: ({ ids }) => {
						this.$emit("input", ids);
						this.$panel.dialog.close();
					}
				}
			});
		},
		remove(index) {
			this.$emit("input", this.value.toSpliced(index, 1));
		},
		removeById(id) {
			this.$emit(
				"input",
				this.value.filter((item) => item !== id)
			);
		}
	}
};
</script>
