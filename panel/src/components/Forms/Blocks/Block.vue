<template>
	<div
		ref="container"
		:class="[
			'k-block-container',
			'k-block-container-fieldset-' + type,
			containerType ? 'k-block-container-type-' + containerType : '',
			$attrs.class
		]"
		:data-batched="isBatched"
		:data-disabled="isDisabled"
		:data-hidden="isHidden"
		:data-id="id"
		:data-last-selected="isLastSelected"
		:data-selected="isSelected"
		:data-translate="fieldset.translate"
		:style="$attrs.style"
		:tabindex="isDisabled ? null : 0"
		@keydown.ctrl.j.prevent.stop="$emit('merge')"
		@keydown.ctrl.alt.down.prevent.stop="$emit('selectDown')"
		@keydown.ctrl.alt.up.prevent.stop="$emit('selectUp')"
		@keydown.ctrl.shift.down.prevent.stop="$emit('sortDown')"
		@keydown.ctrl.shift.up.prevent.stop="$emit('sortUp')"
		@keydown.ctrl.backspace.stop="backspace"
		@focus.stop="onFocus"
		@focusin.stop="onFocusIn"
	>
		<div :class="className" :data-disabled="isDisabled" class="k-block">
			<component
				:is="customComponent"
				ref="editor"
				v-bind="$props"
				:tabs="tabs"
				v-on="listeners"
			/>
		</div>

		<k-block-options
			v-if="!isDisabled"
			ref="options"
			v-bind="{
				isBatched,
				isEditable,
				isFull,
				isHidden,
				isMergable,
				isSplitable: isSplitable()
			}"
			v-on="listenersForOptions"
		/>
	</div>
</template>

<script>
import { props as BlockProps } from "./Types/Default.vue";
import { props as BlockOptionsProps } from "./BlockOptions.vue";

export default {
	mixins: [BlockProps, BlockOptionsProps],
	inheritAttrs: false,
	props: {
		/**
		 * @internal
		 */
		attrs: {
			default: () => ({}),
			type: [Array, Object]
		},
		/**
		 * If `true` the block is the last selected item in a list of batched blocks.  The last one shows the toolbar.
		 */
		isLastSelected: Boolean,
		/**
		 * If `true` the block is marked as selected
		 */
		isSelected: Boolean,
		/**
		 * The name of the block is added to the endpoints
		 */
		name: String,
		/**
		 * The definition of the next block if there's one.
		 */
		next: Object,
		/**
		 * The definition of the previous block if there's one.
		 */
		prev: Object,
		/**
		 * The block type
		 */
		type: String
	},
	emits: [
		"append",
		"chooseToAppend",
		"chooseToConvert",
		"chooseToPrepend",
		"close",
		"copy",
		"duplicate",
		"focus",
		"hide",
		"merge",
		"open",
		"paste",
		"prepend",
		"remove",
		"removeSelected",
		"selectDown",
		"selectUp",
		"show",
		"sortDown",
		"sortUp",
		"split",
		"submit",
		"update"
	],
	computed: {
		className() {
			let className = ["k-block-type-" + this.type];

			if (this.fieldset.preview && this.fieldset.preview !== this.type) {
				className.push("k-block-type-" + this.fieldset.preview);
			}

			if (this.wysiwyg === false) {
				className.push("k-block-type-default");
			}

			return className;
		},
		containerType() {
			const preview = this.fieldset.preview;

			if (preview === false) {
				return false;
			}

			// custom preview
			if (preview) {
				if (this.$helper.isComponent("k-block-type-" + preview)) {
					return preview;
				}
			}

			// default preview
			if (this.$helper.isComponent("k-block-type-" + this.type)) {
				return this.type;
			}

			return false;
		},
		customComponent() {
			if (this.wysiwyg) {
				return this.wysiwygComponent;
			}

			return "k-block-type-default";
		},
		isDisabled() {
			return this.disabled === true || this.fieldset.disabled === true;
		},
		isEditable() {
			return this.fieldset.editable !== false;
		},
		listeners() {
			return {
				append: (event) => this.$emit("append", event),
				chooseToAppend: (event) => this.$emit("chooseToAppend", event),
				chooseToConvert: (event) => this.$emit("chooseToConvert", event),
				chooseToPrepend: (event) => this.$emit("chooseToPrepend", event),
				close: () => this.$emit("close"),
				copy: () => this.$emit("copy"),
				duplicate: () => this.$emit("duplicate"),
				focus: () => this.$emit("focus"),
				hide: () => this.$emit("hide"),
				merge: () => this.$emit("merge"),
				open: (tab) => this.open(tab),
				paste: () => this.$emit("paste"),
				prepend: (event) => this.$emit("prepend", event),
				remove: () => this.remove(),
				removeSelected: () => this.$emit("removeSelected"),
				show: () => this.$emit("show"),
				sortDown: () => this.$emit("sortDown"),
				sortUp: () => this.$emit("sortUp"),
				split: (event) => this.$emit("split", event),
				update: (event) => this.$emit("update", event)
			};
		},
		listenersForOptions() {
			return {
				...this.listeners,
				split: () => this.$refs.editor.split(),
				open: () => {
					if (typeof this.$refs.editor.open === "function") {
						this.$refs.editor.open();
					} else {
						this.open();
					}
				}
			};
		},
		tabs() {
			const tabs = this.fieldset.tabs ?? {};

			for (const [tabName, tab] of Object.entries(tabs)) {
				for (const [fieldName] of Object.entries(tab.fields ?? {})) {
					tabs[tabName].fields[fieldName].section = this.name;
					tabs[tabName].fields[fieldName].endpoints = {
						field:
							this.endpoints.field +
							"/fieldsets/" +
							this.type +
							"/fields/" +
							fieldName,
						section: this.endpoints.section,
						model: this.endpoints.model
					};
				}
			}

			return tabs;
		},
		wysiwyg() {
			return this.wysiwygComponent !== false;
		},
		wysiwygComponent() {
			if (this.containerType) {
				return "k-block-type-" + this.containerType;
			}

			return false;
		}
	},
	methods: {
		backspace(e) {
			// ignore the shortcut when an input is focused
			if (e.target.matches("[contenteditable], input, textarea")) {
				return false;
			}

			e.preventDefault();
			this.remove();
		},
		close() {
			this.$panel.drawer.close(this.id);
		},
		collapse() {
			this.$refs.editor?.collapse?.();
		},
		expand() {
			this.$refs.editor?.expand?.();
		},
		focus() {
			if (typeof this.$refs.editor?.focus === "function") {
				this.$refs.editor.focus();
			} else {
				this.$refs.container?.focus();
			}
		},
		goTo(block) {
			if (block) {
				block.$refs.container?.focus();
				block.open(null, true);
			}
		},
		isSplitable() {
			if (this.isFull === true) {
				return false;
			}

			if (this.$refs.editor) {
				return (
					(this.$refs.editor.isSplitable ?? true) &&
					typeof this.$refs.editor?.split === "function"
				);
			}

			return false;
		},
		onClose() {
			this.$emit("close");
			this.focus();
		},
		onFocus(event) {
			if (this.disabled) {
				return;
			}

			this.$emit("focus", event);
		},
		onFocusIn(event) {
			// skip focus if the event is coming from the options buttons
			// to preserve the current focus (since options buttons directly
			// trigger events and don't need any focus themselves)
			if (this.disabled || this.$refs.options?.$el?.contains(event.target)) {
				return;
			}

			this.$emit("focus", event);
		},
		onInput(value) {
			this.$emit("update", value);
		},
		open(tab, replace = false) {
			if (!this.isEditable || this.isBatched || this.isDisabled) {
				return;
			}

			this.$panel.drawer.open({
				component: "k-block-drawer",
				id: this.id,
				tab: tab,
				on: {
					close: this.onClose,
					input: this.onInput,
					next: () => this.goTo(this.next),
					prev: () => this.goTo(this.prev),
					remove: this.remove,
					show: this.show,
					submit: this.submit
				},
				props: {
					hidden: this.isHidden,
					icon: this.fieldset.icon ?? "box",
					next: this.next,
					prev: this.prev,
					tabs: this.tabs,
					title: this.fieldset.name,
					value: this.content
				},
				replace: replace
			});

			this.$emit("open");
		},
		remove() {
			if (this.isBatched) {
				return this.$emit("removeSelected");
			}

			this.$panel.dialog.open({
				component: "k-remove-dialog",
				props: {
					text: this.$t("field.blocks.delete.confirm")
				},
				on: {
					submit: () => {
						this.$panel.dialog.close();
						this.close();
						this.$emit("remove", this.id);
					}
				}
			});
		},
		show() {
			this.$emit("show");
		},
		submit() {
			this.close();
			this.$emit("submit");
		}
	}
};
</script>

<style>
.k-block-container {
	position: relative;
	padding: var(--spacing-3);
	background: var(--block-color-back);
	border-radius: var(--rounded);
}
.k-block-container:not(:last-of-type) {
	border-bottom: 1px dashed var(--panel-color-back);
}
.k-block-container:focus {
	outline: 0;
}

.k-block-container[data-selected="true"] {
	z-index: 2;
	outline: var(--outline);
	border-bottom-color: transparent;
}
.k-block-container[data-batched="true"]::after {
	position: absolute;
	inset: 0;
	content: "";
	background: hsl(214 33% 77% / 0.175);
	mix-blend-mode: multiply;
}

.k-block-container .k-block-options {
	display: none;
	position: absolute;
	top: 0;
	inset-inline-end: var(--spacing-3);
	margin-top: calc(-1.75rem + 2px);
}
.k-block-container[data-last-selected="true"] > .k-block-options {
	display: flex;
}
.k-block-container[data-hidden="true"] .k-block {
	opacity: 0.25;
}
.k-drawer-options .k-drawer-option[data-disabled="true"] {
	vertical-align: middle;
	display: inline-grid;
}
.k-block-container[data-disabled="true"] {
	background: var(--panel-color-back);
}

/* Collapse long blocks while dragging */
.k-block-container:is(.k-sortable-ghost, .k-sortable-fallback) .k-block {
	position: relative;
	max-height: 4rem;
	overflow: hidden;
}
.k-block-container:is(.k-sortable-ghost, .k-sortable-fallback) .k-block::after {
	position: absolute;
	bottom: 0;
	content: "";
	height: 2rem;
	width: 100%;
	background: linear-gradient(to top, var(--block-color-back), transparent);
}
</style>
