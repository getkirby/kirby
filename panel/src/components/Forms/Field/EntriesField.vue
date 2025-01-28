<template>
	<k-field
		v-bind="$props"
		:class="['k-entries-field', $attrs.class]"
		:style="$attrs.style"
		@click.native.stop
	>
		<!-- Empty State -->
		<k-empty v-if="entries.length === 0" icon="list-bullet" @click="add()">
			{{ empty ?? $t("field.entries.empty") }}
		</k-empty>

		<!-- Entries -->
		<template v-else>
			<k-input-validator
				v-bind="{ min, max, required }"
				:value="JSON.stringify(entries)"
			>
				<k-draggable
					v-bind="dragOptions"
					class="k-entries-field-items"
					@sort="save"
				>
					<div
						v-for="(entry, index) in entries"
						:key="entry.id"
						class="k-entries-field-item"
					>
						<div class="k-entries-field-item-sort-handle">
							<k-button
								v-if="isSortable"
								:title="$t('sort.drag')"
								icon="sort"
								class="k-sort-handle"
								size="sm"
							/>
						</div>
						<div class="k-entries-field-item-input">
							<k-input
								v-bind="field"
								:ref="'entry-' + entry.id"
								:value="entry.value"
								@input="onInput(index, $event)"
							/>
						</div>
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
		</template>
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
			default: () => {}
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

				this.entries = entries.map((value) => {
					return {
						id: this.$helper.uuid(),
						value
					};
				});
			},
			immediate: true
		}
	},
	methods: {
		add(index = null) {
			if (this.more === false || this.disabled === true) {
				return;
			}

			const entry = {
				id: this.$helper.uuid(),
				value: ""
			};

			if (index !== null) {
				this.entries.splice(index, 0, entry);
			} else {
				this.entries.push(entry);
			}

			this.save();
		},
		duplicate(index) {
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
		},
		onInput(index, value) {
			this.entries[index].value = value;
			this.save();
		},
		save() {
			this.$emit("input", this.values);
		},
		remove(index) {
			if (this.disabled === true) {
				return;
			}

			this.entries.splice(index, 1);
			this.save();
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
	--input-color-border: transparent;
	display: flex;
	align-items: center;
	background: var(--color-gray-100);
	border-radius: var(--rounded);
	box-shadow: var(--shadow);
}
.k-entries-field-item-sort-handle {
	display: grid;
	place-items: center;
}
.k-entries-field-item-sort-handle .k-button {
	--button-height: var(--input-height);
	--button-width: var(--input-height);
}
.k-entries-field-item-input {
	flex-grow: 1;
	border-left: 1px solid var(--panel-color-back);
	border-right: 1px solid var(--panel-color-back);
}
.k-entries-field-item-options .k-button {
	--button-height: 100%;
	--button-width: var(--input-height);
}
.k-entries-field-item-options .k-button:not(:last-child) {
	border-right: 1px solid var(--panel-color-back);
}
.k-entries-field-item.k-sortable-ghost {
	outline: var(--outline);
	cursor: grabbing;
}
.k-entries-field-item.k-sortable-fallback {
	opacity: 0 !important;
}
.k-entries-field-footer {
	display: flex;
	justify-content: center;
	margin-top: var(--spacing-3);
}
</style>
