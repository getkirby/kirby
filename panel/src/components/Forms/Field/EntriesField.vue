<template>
	<k-field
		v-bind="$props"
		:class="['k-entries-field', $attrs.class]"
		:style="$attrs.style"
		@click.native.stop
	>
		<!-- Empty State -->
		<k-empty v-if="entries.length === 0" icon="list-bullet" @click="add()">
			{{ empty ?? $t("field.structure.empty") }}
		</k-empty>

		<!-- Entries -->
		<template v-else>
			<div class="k-entries-field-inputs">
				<k-input-validator
					v-bind="{ min, max, required }"
					:value="JSON.stringify(entries)"
				>
					<k-draggable
						v-bind="dragOptions"
						class="k-entries-list"
						@sort="save"
					>
						<k-input
							v-for="(entry, index) in entries"
							v-bind="field"
							:key="index"
							:ref="'entry-' + index"
							:value="entries[index]"
							@input="onInput(index, $event)"
						>
							<template #after>
								<k-button-group layout="collapsed">
									<k-button
										v-if="more"
										:title="$t('add')"
										icon="add"
										size="xs"
										variant="filled"
										tabindex="-1"
										@click="add(index + 1)"
									/>
									<k-button
										v-if="more"
										:title="$t('duplicate')"
										icon="copy"
										size="xs"
										variant="filled"
										tabindex="-1"
										@click="duplicate(index)"
									/>
									<k-button
										:title="$t('remove')"
										icon="remove"
										size="xs"
										variant="filled"
										tabindex="-1"
										@click="remove(index)"
									/>
									<k-button
										v-if="isSortable"
										:title="$t('sort.drag')"
										icon="sort"
										class="k-sort-handle k-sort-button"
										aria-hidden="true"
										size="xs"
										variant="filled"
										tabindex="-1"
									/>
								</k-button-group>
							</template>
						</k-input>
					</k-draggable>
				</k-input-validator>
			</div>
		</template>
		<footer v-if="more">
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
			default: () => {
			}
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
			entries: this.value
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
		}
	},
	watch: {
		value: {
			handler(value) {
				this.entries = value;
			},
			immediate: true
		}
	},
	methods: {
		add(index = null) {
			if (this.more === false || this.disabled === true) {
				return;
			}

			if (index !== null) {
				this.entries.splice(index, 0, "");
			} else {
				this.entries.push("");
			}

			this.save();
		},
		duplicate(index) {
			if (this.more === false || this.disabled === true) {
				return;
			}

			this.entries.splice(index + 1, 0, this.entries[index] ?? "");
			this.save();
		},
		onInput(index, value) {
			this.entries[index] = value;
			this.save();
		},
		save() {
			this.$emit("input", this.entries);
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
.k-entries-field .k-entries-list {
	display: flex;
	flex-direction: column;
	gap: var(--spacing-2);
}

.k-entries-field footer {
	display: flex;
	justify-content: center;
	margin-top: var(--spacing-3);
}
</style>
