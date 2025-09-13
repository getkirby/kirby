<template>
	<k-dialog
		:class="['k-block-selector', $attrs.class]"
		:cancel-button="false"
		:size="size"
		:submit-button="false"
		:style="$attrs.style"
		:visible="true"
		@cancel="$emit('cancel')"
		@submit="$emit('submit', value)"
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
			<k-navigate class="k-block-types">
				<k-button
					v-for="fieldset in group.fieldsets"
					:key="fieldset.name"
					:disabled="disabledFieldsets.includes(fieldset.type)"
					:icon="fieldset.icon ?? 'box'"
					:text="fieldset.name"
					size="lg"
					@click="$emit('submit', fieldset.type)"
					@focus.native="$emit('input', fieldset.type)"
				/>
			</k-navigate>
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
export default {
	inheritAttrs: false,
	props: {
		disabledFieldsets: {
			default: () => [],
			type: Array
		},
		fieldsets: {
			type: Object
		},
		fieldsetGroups: {
			type: Object
		},
		headline: {
			type: String
		},
		size: {
			type: String,
			default: "medium"
		},
		value: {
			default: null,
			type: String
		}
	},
	emits: ["cancel", "close", "input", "paste", "submit"],
	data() {
		return {
			selected: null
		};
	},
	computed: {
		groups() {
			const groups = {};
			let index = 0;

			const fieldsetGroups = this.fieldsetGroups ?? {
				blocks: {
					label: this.$t("field.blocks.fieldsets.label"),
					sets: Object.keys(this.fieldsets)
				}
			};

			for (const key in fieldsetGroups) {
				const group = fieldsetGroups[key];

				group.open = group.open !== false;
				group.fieldsets = group.sets
					.filter((name) => this.fieldsets[name])
					.map((name) => {
						index++;

						return {
							...this.fieldsets[name],
							index
						};
					});

				if (group.fieldsets.length === 0) {
					continue;
				}

				groups[key] = group;
			}

			return groups;
		},
		shortcut() {
			return this.$helper.keyboard.metaKey() + "+v";
		}
	},
	mounted() {
		this.$events.on("paste", this.paste);
	},
	destroyed() {
		this.$events.off("paste", this.paste);
	},
	methods: {
		paste(e) {
			this.$emit("paste", e);
			this.$emit("close");
		}
	}
};
</script>

<style>
.k-block-selector .k-headline {
	margin-bottom: 1rem;
}
.k-block-selector details + details {
	margin-top: var(--spacing-6);
}
.k-block-selector summary {
	font-size: var(--text-xs);
	cursor: pointer;
	color: var(--color-text-dimmed);
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
	--button-color-icon: var(--color-text);
	--button-color-back: light-dark(var(--color-white), var(--color-gray-850));
	--button-padding: var(--spacing-3);
	width: 100%;
	justify-content: start;
	gap: 1rem;
	box-shadow: var(--shadow);
}
.k-block-types .k-button[aria-disabled="true"] {
	opacity: var(--opacity-disabled);
	--button-color-back: transparent;
	box-shadow: none;
}
.k-clipboard-hint {
	padding-top: 1.5rem;
	line-height: var(--leading-normal);
	font-size: var(--text-xs);
	color: var(--color-text-dimmed);
}
.k-clipboard-hint small {
	display: block;
	font-size: inherit;
	color: var(--color-text-dimmed);
}
</style>
