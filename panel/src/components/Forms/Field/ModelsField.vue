<template>
	<k-field
		v-bind="$props"
		:input="id"
		:class="['k-models-field', `k-${$options.type}-field`, $attrs.class]"
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
				v-bind="{ id, min, max, required }"
				:value="JSON.stringify(value)"
			>
				<k-collection
					v-bind="collection"
					@empty="open"
					@sort="onInput"
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
			selected: this.value
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
				sortable: !this.disabled && this.selected.length > 1,
				theme: this.disabled ? "disabled" : null
			};
		},
		hasDropzone() {
			return false;
		},
		more() {
			return !this.max || this.max > this.selected.length;
		}
	},
	watch: {
		value(value) {
			this.selected = value;
		}
	},
	methods: {
		drop() {},
		focus() {},
		onInput() {
			this.$emit("input", this.selected);
		},
		open() {
			if (this.disabled) {
				return false;
			}

			this.$panel.dialog.open({
				component: `k-${this.$options.type}-dialog`,
				props: {
					endpoint: this.endpoints.field,
					hasSearch: this.search,
					max: this.max,
					multiple: this.multiple,
					value: this.selected.map((model) => model.id)
				},
				on: {
					submit: (models) => {
						this.select(models);
						this.$panel.dialog.close();
					}
				}
			});
		},
		remove(index) {
			this.selected.splice(index, 1);
			this.onInput();
		},
		removeById(id) {
			this.selected = this.selected.filter((item) => item.id !== id);
			this.onInput();
		},
		select(items) {
			if (items.length === 0) {
				this.selected = [];
				this.onInput();
				return;
			}

			// remove all items that are no longer selected
			this.selected = this.selected.filter((selected) =>
				items.find((item) => item.id === selected.id)
			);

			// add items that are not yet in the selected list
			for (const item of items) {
				if (!this.selected.find((selected) => item.id === selected.id)) {
					this.selected.push(item);
				}
			}

			this.onInput();
		}
	}
};
</script>

<style>
.k-models-field[data-disabled="true"] .k-item * {
	pointer-events: all !important;
}
</style>
