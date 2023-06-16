<template>
	<k-dialog
		v-bind="$props"
		class="k-block-selector"
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
					:icon="fieldset.icon || 'box'"
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
import Dialog from "@/mixins/dialog.js";

/**
 * @internal
 */
export default {
	inheritAttrs: false,
	mixins: [Dialog],
	props: {
		cancelButton: {
			default: false
		},
		disabledFieldsets: {
			default() {
				return [];
			},
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
			default: "medium"
		},
		submitButton: {
			default: false
		},
		value: {
			default: null,
			type: String
		}
	},
	created() {
		this.$events.$on("paste", this.close);
	},
	destroyed() {
		this.$events.$off("paste", this.close);
	},
	data() {
		return {
			selected: null
		};
	},
	computed: {
		groups() {
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
		shortcut() {
			return this.$helper.keyboard.metaKey() + "+v";
		}
	}
};
</script>

<style>
.k-block-selector.k-dialog {
	background: var(--color-slate-800);
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
	--button-color-icon: var(--color-slate-700);
	--button-color-back: var(--color-slate-900);
	--button-color-hover-back: hsl(
		var(--color-slate-hs),
		calc(var(--color-slate-l-900) - 2%)
	);
	width: 100%;
	justify-content: start;
	gap: 1rem;
	padding-inline: var(--spacing-3);
}
.k-block-types .k-button[aria-disabled] {
	opacity: var(--opacity-disabled);
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
