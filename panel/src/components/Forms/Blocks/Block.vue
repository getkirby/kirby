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
		@keydown.ctrl.j.prevent.stop="$emit('merge')"
		@keydown.ctrl.alt.down.prevent.stop="$emit('selectDown')"
		@keydown.ctrl.alt.up.prevent.stop="$emit('selectUp')"
		@keydown.ctrl.shift.down.prevent.stop="$emit('sortDown')"
		@keydown.ctrl.shift.up.prevent.stop="$emit('sortUp')"
		@keydown.ctrl.backspace.prevent.stop="remove"
		@focus.stop="$emit('focus')"
		@focusin.stop="onFocusIn"
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

		<k-form-drawer
			v-if="isEditable && !isBatched"
			:id="id"
			ref="drawer"
			:icon="fieldset.icon || 'box'"
			:tabs="tabs"
			:title="fieldset.name"
			:value="content"
			class="k-block-drawer"
			@close="onDrawerClose"
			@input="onDrawerInput"
			@open="onDrawerOpen"
			@submit="onDrawerSubmit"
		>
			<template #options>
				<k-button
					v-if="isHidden"
					class="k-drawer-option"
					icon="hidden"
					@click="$emit('show')"
				/>
				<k-button
					:disabled="!prev"
					class="k-drawer-option"
					icon="angle-left"
					@click.prevent.stop="goTo(prev)"
				/>
				<k-button
					:disabled="!next"
					class="k-drawer-option"
					icon="angle-right"
					@click.prevent.stop="goTo(next)"
				/>
				<k-button
					class="k-drawer-option"
					icon="trash"
					@click.prevent.stop="remove"
				/>
			</template>
		</k-form-drawer>
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
			this.$refs.drawer.close();
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
		onDrawerClose() {
			this.$emit("close");
			this.focus();
		},
		onDrawerInput(value) {
			this.$emit("update", value);
		},
		onDrawerOpen() {
			this.$emit("open");
		},
		onDrawerSubmit() {
			this.$emit("submit");
			this.close();
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
		open(tab) {
			this.$refs.drawer?.open(tab);
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
						this.$emit("remove", this.id);
					}
				}
			});
		},
		submit() {
			this.close();
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
	z-index: 2;
	border-bottom-color: transparent;
	box-shadow: var(--color-focus) 0 0 0 1px, var(--color-focus-outline) 0 0 0 3px;
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
