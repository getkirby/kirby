<template>
	<k-field
		v-bind="$props"
		:class="['k-entries-field', $attrs.class]"
		:style="$attrs.style"
		@click.native.stop
	>
		<!-- Options -->
		<template v-if="!disabled" #options>
			<k-button-group layout="collapsed">
				<k-button
					v-if="more"
					:autofocus="autofocus"
					:responsive="true"
					:text="$t('add')"
					icon="add"
					variant="filled"
					size="xs"
					class="input-focus"
					@click="add()"
				/>
				<k-button
					icon="dots"
					variant="filled"
					size="xs"
					@click="$refs.options.toggle()"
				/>
				<k-dropdown-content ref="options" :options="options" align-x="end" />
			</k-button-group>
		</template>

		<k-input-validator
			v-bind="{ min, max, required }"
			:value="JSON.stringify(entries)"
		>
			<!-- Empty State -->
			<k-empty v-if="entries.length === 0" icon="list-bullet" @click="add()">
				{{ empty ?? $t("field.entries.empty") }}
			</k-empty>

			<!-- Entries -->
			<k-draggable
				v-else
				v-bind="dragOptions"
				class="k-entries-field-items"
				@sort="save"
			>
				<div
					v-for="(entry, index) in entries"
					:key="entry.id"
					class="k-entries-field-item"
				>
					<k-button
						v-if="isSortable"
						:ref="'entry-' + index + '-sort-handle'"
						:title="$t('sort.drag')"
						icon="sort"
						class="k-sort-handle k-entries-field-item-sort-handle"
						size="sm"
						@keydown.up.native="sortUp(index)"
						@keydown.down.native="sortDown(index)"
					/>
					<component
						:is="`k-${field.type}-field`"
						:ref="'entry-' + index + '-input'"
						:disabled="disabled"
						:label="false"
						:value="entry.value"
						v-bind="field"
						class="k-entries-field-item-input"
						@input="onInput(index, $event)"
					/>
					<k-button-group
						class="k-entries-field-item-options"
						layout="collapsed"
					>
						<k-button
							v-if="more"
							:title="$t('add')"
							icon="add"
							size="sm"
							@click="add(index + 1)"
						/>
						<k-button
							v-if="more"
							:title="$t('duplicate')"
							icon="copy"
							size="sm"
							@click="duplicate(index)"
						/>
						<k-button
							v-if="!disabled"
							:title="$t('remove')"
							icon="trash"
							size="sm"
							@click="remove(index)"
						/>
					</k-button-group>
				</div>
			</k-draggable>
		</k-input-validator>

		<footer v-if="more" class="k-entries-field-footer">
			<k-button
				:title="$t('add')"
				icon="add"
				size="xs"
				variant="filled"
				@click="add()"
			/>
		</footer>
	</k-field>
</template>

<script>
import { props as Field } from "../Field.vue";
import { props as Input } from "../Input.vue";

export default {
	mixins: [Field, Input],
	inheritAttrs: false,
	props: {
		/**
		 * The text, that is shown when the field has no entries.
		 */
		empty: String,
		/**
		 * Field attrs for the form
		 */
		field: {
			type: Object,
			default: () => ({})
		},
		/**
		 * Upper limit of entries allowed
		 */
		max: Number,
		/**
		 * Lower limit of entries required
		 */
		min: Number,
		/**
		 * Whether to allow sorting of entries
		 */
		sortable: {
			type: Boolean,
			default: true
		},
		value: {
			type: Array,
			default: () => []
		}
	},
	emits: ["input", "sort"],
	data() {
		return {
			entries: []
		};
	},
	computed: {
		/**
		 * Returns draggable options
		 * @returns {object}
		 */
		dragOptions() {
			return {
				disabled: this.isSortable === false,
				handle: true,
				list: this.entries,
				sort: this.isSortable
			};
		},
		/**
		 * Returns whether the entries can be sorted
		 * @returns {boolean}
		 */
		isSortable() {
			if (this.disabled === true) {
				return false;
			}

			if (this.entries.length <= 1) {
				return false;
			}

			if (this.sortable === false) {
				return false;
			}

			return true;
		},
		/**
		 * Returns if new entries can be added
		 * @returns {boolean}
		 */
		more() {
			if (this.disabled === true) {
				return false;
			}

			if (this.max && this.entries.length >= this.max) {
				return false;
			}

			return true;
		},
		options() {
			return [
				{
					disabled: this.entries.length === 0 || this.disabled,
					icon: "template",
					text: this.$t("copy.all"),
					click: this.copyAll
				},
				"-",
				{
					disabled: this.entries.length === 0 || this.disabled,
					icon: "trash",
					text: this.$t("delete.all"),
					click: this.removeAll
				}
			];
		},
		values() {
			return this.entries.map((entry) => entry.value);
		}
	},
	watch: {
		value: {
			handler(entries) {
				// no need to add ids again if the values are the same
				if (entries === this.values) {
					return;
				}

				this.entries = entries.map((value) => ({
					id: this.$helper.uuid(),
					value
				}));
			},
			immediate: true
		}
	},
	methods: {
		async add(index = null, value = null) {
			if (this.more === false || this.disabled === true) {
				return;
			}

			value ??= this.$helper.field.form({ field: this.field })?.field;

			const entry = {
				id: this.$helper.uuid(),
				value: value ?? ""
			};

			index ??= this.entries.length;
			this.entries.splice(index, 0, entry);

			this.save();

			await this.$nextTick();
			this.focus(index);
		},
		copyAll() {
			const copy = this.values.map((value) => "- " + value).join("\n");

			this.$helper.clipboard.write(copy);
			this.$panel.notification.success(this.$t("copy.success"));
		},
		async duplicate(index) {
			if (
				this.more === false ||
				this.disabled === true ||
				this.entries[index] === undefined
			) {
				return;
			}

			const duplicate = {
				...this.entries[index],
				id: this.$helper.uuid()
			};

			this.entries.splice(index + 1, 0, duplicate);
			this.save();

			await this.$nextTick();
			this.focus(index + 1);
		},
		focus(index, on = "input") {
			this.$refs["entry-" + index + "-" + on]?.[0]?.focus?.();
		},
		onInput(index, value) {
			this.entries[index].value = value;
			this.save();
		},
		remove(index) {
			if (this.disabled === true) {
				return;
			}

			this.entries.splice(index, 1);
			this.save();
			this.focus(index - 1);
		},
		removeAll() {
			this.$panel.dialog.open({
				component: "k-remove-dialog",
				props: {
					text: this.$t("field.entries.delete.confirm.all")
				},
				on: {
					submit: () => {
						this.entries = [];
						this.save();
						this.$panel.dialog.close();
					}
				}
			});
		},
		save() {
			this.$emit("input", this.values);
		},
		async sort(index, direction) {
			if (this.isSortable === false) {
				return;
			}

			const entry = this.entries[index];
			this.entries.splice(index, 1);
			this.entries.splice(index + direction, 0, entry);
			this.save();
			await this.$nextTick();
			this.focus(index + direction, "sort-handle");
		},
		sortDown(index) {
			if (index >= this.entries.length - 1) {
				return;
			}

			this.sort(index, 1);
		},
		sortUp(index) {
			if (index <= 0) {
				return;
			}

			this.sort(index, -1);
		}
	}
};
</script>

<style>
.k-entries-field-items {
	display: flex;
	flex-direction: column;
	gap: 2px;
}

.k-entries-field-item {
	height: var(--input-height);
	display: flex;
	align-items: center;
	background: var(--input-color-back);
	border-radius: var(--rounded);
}

.k-entries-field:not([data-disabled="true"]) .k-entries-field-item {
	--input-color-border: transparent;
	box-shadow: var(--shadow);
}

.k-entries-field-item-sort-handle.k-button {
	--button-height: var(--input-height);
	--button-width: var(--input-height);
}

.k-entries-field-item-input {
	flex-grow: 1;
	border-inline: 1px solid var(--panel-color-back);
}

.k-entries-field-item-options .k-button {
	--button-height: 100%;
	--button-width: var(--input-height);
}

@container (max-width: 30rem) {
	.k-entries-field-item-options > .k-button:not(:last-of-type) {
		display: none;
	}
}

.k-entries-field-item-options .k-button:has(+ .k-button) {
	border-right: 1px solid var(--panel-color-back);
}

.k-entries-field-item.k-sortable-ghost {
	outline: var(--outline);
	cursor: grabbing;
}

.k-entries-field-item.k-sortable-fallback {
	display: none;
}

.k-entries-field-footer {
	display: flex;
	justify-content: center;
	margin-top: var(--spacing-3);
}
</style>
