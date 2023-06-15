<template>
	<div
		ref="container"
		:class="'k-block-container-type-' + type"
		:data-batched="isBatched"
		:data-disabled="fieldset.disabled"
		:data-hidden="isHidden"
		:data-id="id"
		:data-last-selected="isLastSelected"
		:data-selected="isSelected"
		:data-translate="fieldset.translate"
		class="k-block-container"
		tabindex="0"
		@keydown.meta.j.prevent="$emit('merge')"
		@keydown.ctrl.j.prevent="$emit('merge')"
		@keydown.meta.up.exact.prevent="$emit('focusPrev')"
		@keydown.ctrl.up.exact.prevent="$emit('focusPrev')"
		@keydown.meta.down.exact.prevent="$emit('focusNext')"
		@keydown.ctrl.down.exact.prevent="$emit('focusNext')"
		@keydown.meta.alt.down.prevent="$emit('selectDown')"
		@keydown.ctrl.alt.down.prevent="$emit('selectDown')"
		@keydown.meta.alt.up.prevent="$emit('selectUp')"
		@keydown.ctrl.alt.up.prevent="$emit('selectUp')"
		@keydown.meta.shift.down.prevent="$emit('sortDown')"
		@keydown.ctrl.shift.down.prevent="$emit('sortDown')"
		@keydown.meta.shift.up.prevent="$emit('sortUp')"
		@keydown.ctrl.shift.up.prevent="$emit('sortUp')"
		@keydown.meta.backspace.prevent="remove"
		@keydown.ctrl.backspace.prevent="remove"
		@focus="$emit('focus')"
		@focusin="onFocusIn"
	>
		<div :class="className" class="k-block">
			<component
				:is="customComponent"
				ref="editor"
				v-bind="$props"
				v-on="listeners"
			/>
		</div>

		<k-block-options
			ref="options"
			:is-batched="isBatched"
			:is-editable="isEditable"
			:is-full="isFull"
			:is-hidden="isHidden"
			:is-mergable="isMergable"
			:is-splitable="isSplitable()"
			v-on="{
				...listeners,
				split: () => $refs.editor.split()
			}"
		/>
	</div>
</template>

<script>
export default {
	inheritAttrs: false,
	props: {
		attrs: [Array, Object],
		content: [Array, Object],
		endpoints: Object,
		fieldset: Object,
		id: String,
		isBatched: Boolean,
		isFull: Boolean,
		isHidden: Boolean,
		isLastSelected: Boolean,
		isMergable: Boolean,
		isSelected: Boolean,
		name: String,
		next: Object,
		prev: Object,
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
		"focusPrev",
		"focusNext",
		"hide",
		"merge",
		"open",
		"paste",
		"prepend",
		"remove",
		"selectDown",
		"selectUp",
		"show",
		"sortDown",
		"sortUp",
		"split",
		"submit",
		"update",
		"confirmToRemoveSelected"
	],
	data() {
		return {
			skipFocus: false
		};
	},
	computed: {
		className() {
			let className = ["k-block-type-" + this.type];

			if (this.fieldset.preview !== this.type) {
				className.push("k-block-type-" + this.fieldset.preview);
			}

			if (this.wysiwyg === false) {
				className.push("k-block-type-default");
			}

			return className;
		},
		customComponent() {
			if (this.wysiwyg) {
				return this.wysiwygComponent;
			}

			return "k-block-type-default";
		},
		isEditable() {
			return this.fieldset.editable !== false;
		},
		listeners() {
			return {
				append: ($event) => this.$emit("append", $event),
				chooseToAppend: ($event) => this.$emit("chooseToAppend", $event),
				chooseToConvert: ($event) => this.$emit("chooseToConvert", $event),
				chooseToPrepend: ($event) => this.$emit("chooseToPrepend", $event),
				close: () => this.$emit("close"),
				copy: () => this.$emit("copy"),
				duplicate: () => this.$emit("duplicate"),
				focus: () => this.$emit("focus"),
				hide: () => this.$emit("hide"),
				merge: () => this.$emit("merge"),
				open: () => this.open(),
				paste: () => this.$emit("paste"),
				prepend: ($event) => this.$emit("prepend", $event),
				remove: () => this.remove(),
				removeSelected: () => this.$emit("removeSelected"),
				show: () => this.$emit("show"),
				sortDown: () => this.$emit("sortDown"),
				sortUp: () => this.$emit("sortUp"),
				split: ($event) => this.$emit("split", $event),
				update: ($event) => this.$emit("update", $event)
			};
		},
		tabs() {
			let tabs = this.fieldset.tabs;

			Object.entries(tabs).forEach(([tabName, tab]) => {
				Object.entries(tab.fields).forEach(([fieldName]) => {
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
				});
			});

			return tabs;
		},
		wysiwyg() {
			return this.wysiwygComponent !== false;
		},
		wysiwygComponent() {
			const preview = this.fieldset.preview;

			if (preview === false) {
				return false;
			}

			let component;

			// custom preview
			if (preview) {
				component = "k-block-type-" + preview;

				if (this.$helper.isComponent(component)) {
					return component;
				}
			}

			// default preview
			component = "k-block-type-" + this.type;

			if (this.$helper.isComponent(component)) {
				return component;
			}

			return false;
		}
	},
	methods: {
		close() {
			this.$panel.drawer.close();
		},
		focus() {
			if (this.skipFocus !== true) {
				if (typeof this.$refs.editor.focus === "function") {
					this.$refs.editor.focus();
				} else {
					this.$refs.container.focus();
				}
			}
		},
		goTo(block) {
			if (block) {
				this.skipFocus = true;
				this.close();

				this.$nextTick(() => {
					block.$refs.container.focus();
					block.open();
					this.skipFocus = false;
				});
			}
		},
		isSplitable() {
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
		onFocusIn(event) {
			// skip focus if the event is coming from the options buttons
			// to preserve the current focus (since options buttons directly
			// trigger events and don't need any focus themselves)
			if (this.$refs.options?.$el?.contains(event.target)) {
				return;
			}

			this.$emit("focus", event);
		},
		onInput(value) {
			this.$emit("update", value);
		},
		open(tab) {
			if (!this.isEditable || this.isBatched) {
				return;
			}

			this.$panel.drawer.open({
				component: "k-block-drawer",
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
					id: this.id,
					next: this.next,
					prev: this.prev,
					tabs: this.tabs,
					title: this.fieldset.name,
					value: this.content
				}
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
						this.$panel.drawer.close();
						this.$panel.dialog.close();
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
	padding: 0.75rem;
	background: var(--color-white);
	border-radius: var(--rounded);
}
.k-block-container:not(:last-of-type) {
	border-bottom: 1px dashed rgba(0, 0, 0, 0.1);
}
.k-block-container:focus {
	outline: 0;
}

.k-block-container[data-selected="true"] {
	transform: translate(0);
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
	inset-inline-end: 0.75rem;
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
[data-disabled="true"] .k-block-container {
	background: var(--color-background);
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
	background: linear-gradient(to top, var(--color-white), transparent);
}
</style>
