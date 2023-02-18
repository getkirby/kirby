<template>
	<k-dialog
		ref="dialog"
		:cancel-button="false"
		:submit-button="false"
		class="k-block-selector"
		size="medium"
		@open="onOpen"
		@close="onClose"
	>
		<k-headline v-if="headline">
			{{ headline }}
		</k-headline>
		<details
			v-for="(group, groupName) in groups"
			:key="groupName"
			:open="group.open"
		>
			<summary>{{ group.label }}</summary>
			<div class="k-block-types">
				<k-button
					v-for="fieldset in group.fieldsets"
					:ref="'fieldset-' + fieldset.index"
					:key="fieldset.name"
					:disabled="disabled.includes(fieldset.type)"
					:icon="fieldset.icon || 'box'"
					:text="fieldset.name"
					@keydown.up="navigate(fieldset.index - 1)"
					@keydown.down="navigate(fieldset.index + 1)"
					@click="add(fieldset.type)"
				/>
			</div>
		</details>
		<!-- eslint-disable vue/no-v-html -->
		<p
			class="k-clipboard-hint"
			v-html="$t('field.blocks.fieldsets.paste', { shortcut })"
		/>
		<!-- eslint-enable -->
	</k-dialog>
</template>

<script>
/**
 * @internal
 */
export default {
	inheritAttrs: false,
	props: {
		endpoint: String,
		fieldsets: Object,
		fieldsetGroups: Object
	},
	data() {
		return {
			dialogIsOpen: false,
			disabled: [],
			headline: null,
			payload: null,
			event: "add",
			groups: this.createGroups()
		};
	},
	computed: {
		shortcut() {
			return this.$helper.keyboard.metaKey() + "+v";
		}
	},
	methods: {
		add(type) {
			this.$emit(this.event, type, this.payload);
			this.$refs.dialog.close();
		},
		close() {
			this.$refs.dialog.close();
		},
		createGroups() {
			let groups = {};
			let index = 0;

			const fieldsetGroups = this.fieldsetGroups || {
				blocks: {
					label: this.$t("field.blocks.fieldsets.label"),
					sets: Object.keys(this.fieldsets)
				}
			};

			Object.keys(fieldsetGroups).forEach((key) => {
				let group = fieldsetGroups[key];

				group.open = group.open === false ? false : true;
				group.fieldsets = group.sets
					.filter((fieldsetName) => this.fieldsets[fieldsetName])
					.map((fieldsetName) => {
						index++;

						return {
							...this.fieldsets[fieldsetName],
							index
						};
					});

				if (group.fieldsets.length === 0) {
					return;
				}

				groups[key] = group;
			});

			return groups;
		},
		isOpen() {
			return this.dialogIsOpen;
		},
		navigate(index) {
			this.$refs["fieldset-" + index]?.[0]?.focus();
		},
		onClose() {
			this.dialogIsOpen = false;
			this.$events.$off("paste", this.close);
		},
		onOpen() {
			this.dialogIsOpen = true;
			this.$events.$on("paste", this.close);
		},
		open(payload, params = {}) {
			const options = {
				event: "add",
				disabled: [],
				headline: null,
				...params
			};

			this.event = options.event;
			this.disabled = options.disabled;
			this.headline = options.headline;
			this.payload = payload;
			this.$refs.dialog.open();
		}
	}
};
</script>

<style>
.k-block-selector.k-dialog {
	background: var(--color-dark);
	color: var(--color-white);
}
.k-block-selector .k-headline {
	margin-bottom: 1rem;
}
.k-block-selector details + details {
	margin-top: var(--spacing-6);
}
.k-block-selector summary {
	font-size: var(--text-xs);
	cursor: pointer;
	color: var(--color-gray-400);
}
.k-block-selector details:only-of-type summary {
	pointer-events: none;
}
.k-block-selector summary:focus {
	outline: 0;
}
.k-block-selector summary:focus-visible {
	color: var(--color-focus);
}
.k-block-types {
	display: grid;
	grid-gap: 2px;
	margin-top: 0.75rem;
	grid-template-columns: repeat(1, 1fr);
}
.k-block-types .k-button {
	--button-color-back: rgba(0, 0, 0, 0.5);
	--button-color-hover: rgba(0, 0, 0, 0.3);
	width: 100%;
	justify-content: start;
	gap: 1rem;
	padding-inline: var(--spacing-3);
}
.k-block-types .k-button:focus {
	outline: 2px solid var(--color-focus);
}
.k-clipboard-hint {
	padding-top: 1.5rem;
	font-size: var(--text-xs);
	color: var(--color-gray-400);
}
.k-clipboard-hint kbd {
	background: rgba(0, 0, 0, 0.5);
	font-family: var(--font-mono);
	letter-spacing: 0.1em;
	padding: 0.25rem;
	border-radius: var(--rounded);
	margin: 0 0.25rem;
}
.k-clipboard-hint small {
	display: block;
	margin-top: 0.5rem;
	color: var(--color-gray-500);
}
</style>
