<template>
	<div
		:class="['k-blocks', $attrs.class]"
		:data-disabled="disabled"
		:data-empty="blocks.length === 0"
		:style="$attrs.style"
	>
		<template v-if="hasFieldsets">
			<k-draggable
				v-bind="draggableOptions"
				:data-multi-select-key="isMultiSelectKey"
				class="k-blocks-list"
				@sort="save"
			>
				<k-block
					v-for="(block, index) in blocks"
					:ref="'block-' + block.id"
					:key="block.id"
					v-bind="{
						...block,
						disabled,
						endpoints,
						fieldset: fieldset(block),
						isBatched: isSelected(block) && selected.length > 1,
						isFull,
						isHidden: block.isHidden === true,
						isLastSelected: isLastSelected(block),
						isMergable,
						isSelected: isSelected(block),
						next: prevNext(index + 1),
						prev: prevNext(index - 1)
					}"
					@append="add($event, index + 1)"
					@chooseToAppend="choose(index + 1)"
					@chooseToConvert="chooseToConvert(block)"
					@chooseToPrepend="choose(index)"
					@click.native="onClickBlock(block, $event)"
					@close="isEditing = false"
					@copy="copy()"
					@duplicate="duplicate(block, index)"
					@focus="onFocus(block)"
					@hide="hide(block)"
					@merge="merge()"
					@open="isEditing = true"
					@paste="pasteboard()"
					@prepend="add($event, index)"
					@remove="remove(block)"
					@removeSelected="removeSelected"
					@show="show(block)"
					@selectDown="selectDown"
					@selectUp="selectUp"
					@sortDown="sort(block, index, index + 1)"
					@sortUp="sort(block, index, index - 1)"
					@split="split(block, index, $event)"
					@update="update(block, $event)"
				/>
			</k-draggable>

			<!-- No blocks -->
			<k-empty class="k-blocks-empty" icon="box" @click="choose(blocks.length)">
				{{ empty ?? $t("field.blocks.empty") }}
			</k-empty>
		</template>

		<!-- No fieldsets -->
		<k-empty v-else icon="box">
			{{ $t("field.blocks.fieldsets.empty") }}
		</k-empty>
	</div>
</template>

<script>
import { set } from "vue";
import { autofocus, disabled, id } from "@/mixins/props.js";

export const props = {
	mixins: [autofocus, disabled, id],
	props: {
		empty: String,
		endpoints: Object,
		fieldsets: Object,
		fieldsetGroups: Object,
		group: String,
		min: {
			type: Number,
			default: null
		},
		max: {
			type: Number,
			default: null
		},
		value: {
			type: Array,
			default: () => []
		}
	},
	emits: ["input"]
};

export default {
	mixins: [props],
	inheritAttrs: false,
	data() {
		return {
			blocks: this.value ?? [],
			isEditing: false,
			isMultiSelectKey: false,
			selected: []
		};
	},
	computed: {
		draggableOptions() {
			return {
				handle: ".k-sort-handle",
				list: this.blocks,
				group: this.group,
				move: this.move,
				data: {
					fieldsets: this.fieldsets,
					isFull: this.isFull
				}
			};
		},
		hasFieldsets() {
			return this.$helper.object.length(this.fieldsets) > 0;
		},
		isEmpty() {
			return this.blocks.length === 0;
		},
		isFull() {
			if (this.max === null) {
				return false;
			}

			return this.blocks.length >= this.max;
		},
		isMergable() {
			if (this.selected.length < 2) {
				return false;
			}

			const blocks = this.selected.map((id) => this.find(id));
			const types = new Set(blocks.map((block) => block.type));

			if (types.size > 1) {
				return false;
			}

			return typeof this.ref(blocks[0]).$refs.editor.merge === "function";
		}
	},
	watch: {
		value() {
			this.blocks = this.value;
		}
	},
	mounted() {
		// focus first block
		if (this.$props.autofocus === true) {
			setTimeout(this.focus, 100);
		}

		this.$events.on("blur", this.onBlur);
		this.$events.on("click", this.onClickGlobal);
		this.$events.on("copy", this.onCopy);
		this.$events.on("keydown", this.onKey);
		this.$events.on("keyup", this.onKey);
		this.$events.on("paste", this.onPaste);
	},
	destroyed() {
		this.$events.off("blur", this.onBlur);
		this.$events.off("click", this.onClickGlobal);
		this.$events.off("copy", this.onCopy);
		this.$events.off("keydown", this.onKey);
		this.$events.off("keyup", this.onKey);
		this.$events.off("paste", this.onPaste);
	},
	methods: {
		async add(type = "text", index) {
			const block = await this.$api.get(
				this.endpoints.field + "/fieldsets/" + type
			);
			this.blocks.splice(index, 0, block);
			this.save();

			await this.$nextTick();
			this.focusOrOpen(block);
		},
		choose(index) {
			if (this.$helper.object.length(this.fieldsets) === 1) {
				return this.add(Object.values(this.fieldsets)[0].type, index);
			}

			this.$panel.dialog.open({
				component: "k-block-selector",
				props: {
					fieldsetGroups: this.fieldsetGroups,
					fieldsets: this.fieldsets
				},
				on: {
					submit: (type) => {
						this.add(type, index);
						this.$panel.dialog.close();
					},
					paste: (e) => {
						this.paste(e, index);
					}
				}
			});
		},
		chooseToConvert(block) {
			this.$panel.dialog.open({
				component: "k-block-selector",
				props: {
					disabledFieldsets: [block.type],
					fieldsetGroups: this.fieldsetGroups,
					fieldsets: this.fieldsets,
					headline: this.$t("field.blocks.changeType")
				},
				on: {
					submit: (type) => {
						this.convert(type, block);
						this.$panel.dialog.close();
					},
					paste: this.paste
				}
			});
		},
		collapse(block) {
			const ref = this.ref(block);
			ref?.collapse();
		},
		collapseAll() {
			for (const block of this.blocks) {
				this.collapse(block);
			}
		},
		copy(e) {
			// don't copy when there are no blocks yet
			if (this.blocks.length === 0) {
				return false;
			}

			// don't copy when nothing is selected
			if (this.selected.length === 0) {
				return false;
			}

			let blocks = [];

			for (const block of this.blocks) {
				if (this.selected.includes(block.id)) {
					blocks.push(block);
				}
			}

			// don't copy if no blocks could be found
			if (blocks.length === 0) {
				return false;
			}

			this.$helper.clipboard.write(blocks, e);

			// reselect the previously focussed elements
			this.selected = blocks.map((block) => block.id);

			// a sign that it has been copied
			this.$panel.notification.success({
				message: this.$t("copy.success.multiple", { count: blocks.length }),
				icon: "template"
			});
		},
		copyAll() {
			this.selectAll();
			this.copy();
			this.deselectAll();
		},
		async convert(type, block) {
			const index = this.findIndex(block.id);

			if (index === -1) {
				return false;
			}

			const fields = (fieldset) => {
				let fields = {};

				for (const tab of Object.values(fieldset?.tabs ?? {})) {
					fields = {
						...fields,
						...tab.fields
					};
				}

				return fields;
			};

			const oldBlock = this.blocks[index];
			const newBlock = await this.$api.get(
				this.endpoints.field + "/fieldsets/" + type
			);

			const oldFieldset = this.fieldsets[oldBlock.type];
			const newFieldset = this.fieldsets[type];

			if (!newFieldset) {
				return false;
			}

			let content = newBlock.content;

			const newFields = fields(newFieldset);
			const oldFields = fields(oldFieldset);

			for (const [name, field] of Object.entries(newFields)) {
				const oldField = oldFields[name];

				if (oldField?.type === field.type && oldBlock?.content?.[name]) {
					content[name] = oldBlock.content[name];
				}
			}

			this.blocks[index] = {
				...newBlock,
				id: oldBlock.id,
				content: content
			};

			this.save();
		},
		deselect(block) {
			const index = this.selected.findIndex((id) => id === block.id);

			if (index !== -1) {
				this.selected.splice(index, 1);
			}
		},
		deselectAll() {
			this.selected = [];
		},
		async duplicate(block, index) {
			const copy = {
				...this.$helper.object.clone(block),
				id: this.$helper.uuid()
			};
			this.blocks.splice(index + 1, 0, copy);
			this.save();
		},
		expand(block) {
			const ref = this.ref(block);
			ref?.expand();
		},
		expandAll() {
			for (const block of this.blocks) {
				this.expand(block);
			}
		},
		fieldset(block) {
			return (
				this.fieldsets[block.type] ?? {
					icon: "box",
					name: block.type,
					tabs: {
						content: {
							fields: {}
						}
					},
					type: block.type
				}
			);
		},
		find(id) {
			return this.blocks.find((element) => element.id === id);
		},
		findIndex(id) {
			return this.blocks.findIndex((element) => element.id === id);
		},
		focus(block) {
			const ref = this.ref(block);
			this.selected = [block?.id ?? this.blocks[0]];
			ref?.focus();
			ref?.$el.scrollIntoView({ block: "nearest" });
		},
		focusOrOpen(block) {
			if (this.fieldsets[block.type].wysiwyg) {
				this.focus(block);
			} else {
				this.open(block);
			}
		},
		hide(block) {
			set(block, "isHidden", true);
			this.save();
		},
		isInputEvent() {
			const focused = document.querySelector(":focus");
			return focused?.matches(
				"input, textarea, [contenteditable], .k-writer-input"
			);
		},
		isLastSelected(block) {
			const [lastItem] = this.selected.slice(-1);
			return lastItem && block.id === lastItem;
		},
		isOnlyInstance() {
			return document.querySelectorAll(".k-blocks").length === 1;
		},
		isSelected(block) {
			return this.selected.includes(block.id);
		},
		async merge() {
			if (this.isMergable) {
				const blocks = this.selected.map((id) => this.find(id));

				// top selected block handles merging
				// (will update its own content with merged content)
				this.ref(blocks[0]).$refs.editor.merge(blocks);

				// remove all other selected blocks
				for (const block of blocks.slice(1)) {
					this.remove(block);
				}

				await this.$nextTick();
				this.focus(blocks[0]);
			}
		},
		move(event) {
			// moving block between fields
			if (event.from !== event.to) {
				const block = event.draggedData;
				const to = event.toData;

				// fieldset is not supported in target field
				if (Object.keys(to.fieldsets).includes(block.type) === false) {
					return false;
				}

				// target field has already reached max number of blocks
				if (to.isFull === true) {
					return false;
				}
			}

			return true;
		},
		onBlur() {
			// resets multi selecting on tab change
			// keep only if there are already multiple selections
			// triggers `blur` event when tab changed
			if (this.selected.length === 0) {
				this.isMultiSelectKey = false;
			}
		},
		onClickBlock(block, event) {
			// checks the event just before selecting the block
			// especially since keyup doesn't trigger in with
			// `ctrl/alt/cmd + tab` or `ctrl/alt/cmd + click` combinations
			// for ex: clicking outside of webpage or another browser tab
			if (event && this.isMultiSelectKey) {
				this.onKey(event);
			}

			if (this.isMultiSelectKey) {
				event.preventDefault();
				event.stopPropagation();

				if (this.isSelected(block)) {
					this.deselect(block);
				} else {
					this.select(block);
				}
			}
		},
		onClickGlobal(event) {
			// ignore focus in dialogs or drawers to keep the current selection
			if (
				typeof event.target.closest === "function" &&
				(event.target.closest(".k-dialog") || event.target.closest(".k-drawer"))
			) {
				return;
			}

			const overlay = document.querySelector(".k-overlay:last-of-type");

			if (
				this.$el.contains(event.target) === false &&
				overlay?.contains(event.target) === false
			) {
				this.deselectAll();
				return;
			}

			// since we are still working in the same block when overlay is open
			// we cannot detect the transition between the layout columns;
			// following codes detect if the target is in the same column
			if (
				overlay &&
				this.$el.closest(".k-layout-column")?.contains(event.target) === false
			) {
				this.deselectAll();
				return;
			}
		},
		onCopy(event) {
			if (
				// only act on copy events for this blocks component
				this.$el.contains(event.target) === false ||
				// don't copy when the drawer or any dialogs are open
				this.isEditing === true ||
				this.$panel.dialog.isOpen === true ||
				// don't copy if an input is focused
				this.isInputEvent(event) === true
			) {
				return false;
			}

			return this.copy(event);
		},
		onFocus(block) {
			if (this.isMultiSelectKey === false) {
				this.selected = [block.id];
			}
		},
		async onKey(event) {
			this.isMultiSelectKey = event.metaKey || event.ctrlKey || event.altKey;

			// remove batch selecting on escape, only select first one
			if (event.code === "Escape" && this.selected.length > 1) {
				const block = this.find(this.selected[0]);
				await this.$nextTick();
				this.focus(block);
			}
		},
		onPaste(e) {
			// never paste blocks when the focus is in an input element
			if (this.isInputEvent(e) === true) {
				return false;
			}

			// not when any dialogs or drawers are open
			if (this.isEditing === true || this.$panel.dialog.isOpen === true) {
				return false;
			}

			// not when nothing is selected and the paste event
			// doesn't target something in the block component
			if (this.selected.length === 0 && this.$el.contains(e.target) === false) {
				return false;
			}

			return this.paste(e);
		},
		open(block) {
			this.$refs["block-" + block.id]?.[0].open();
		},
		async paste(e, index) {
			const html = this.$helper.clipboard.read(e);

			// pass html or plain text to the paste endpoint to convert it to blocks
			let blocks = await this.$api.post(this.endpoints.field + "/paste", {
				html: html
			});

			// get the index
			if (index === undefined) {
				let item = this.selected[this.selected.length - 1];
				index = this.findIndex(item);

				if (index === -1) {
					index = this.blocks.length;
				}

				index++;
			}

			// don't add blocks that exceed the maximum limit
			if (this.max) {
				const max = this.max - this.blocks.length;
				blocks = blocks.slice(0, max);
			}

			this.blocks.splice(index, 0, ...blocks);
			this.save();

			// a sign that it has been pasted
			this.$panel.notification.success({
				message: this.$t("paste.success", { count: blocks.length }),
				icon: "download"
			});
		},
		pasteboard() {
			this.$panel.dialog.open({
				component: "k-block-pasteboard",
				on: {
					paste: this.paste
				}
			});
		},
		prevNext(index) {
			if (this.blocks[index]) {
				return this.$refs["block-" + this.blocks[index].id]?.[0];
			}
		},
		ref(block) {
			return this.$refs["block-" + (block?.id ?? this.blocks[0]?.id)]?.[0];
		},
		remove(block) {
			const index = this.findIndex(block.id);

			if (index !== -1) {
				this.deselect(block);
				this.$delete(this.blocks, index);
				this.save();
			}
		},
		removeAll() {
			this.$panel.dialog.open({
				component: "k-remove-dialog",
				props: {
					text: this.$t("field.blocks.delete.confirm.all"),
					submitButton: this.$t("delete.all")
				},
				on: {
					submit: () => {
						this.selected = [];
						this.blocks = [];
						this.save();
						this.$panel.dialog.close();
					}
				}
			});
		},
		removeSelected() {
			this.$panel.dialog.open({
				component: "k-remove-dialog",
				props: {
					text: this.$t("field.blocks.delete.confirm.selected")
				},
				on: {
					submit: () => {
						for (const id of this.selected) {
							const index = this.findIndex(id);
							if (index !== -1) {
								this.$delete(this.blocks, index);
							}
						}

						this.deselectAll();
						this.save();

						this.$panel.dialog.close();
					}
				}
			});
		},
		save() {
			this.$emit("input", this.blocks);
		},
		select(block) {
			if (this.isSelected(block) === false) {
				this.selected.push(block.id);
			}
		},
		selectDown() {
			const last = this.selected[this.selected.length - 1];
			const index = this.findIndex(last) + 1;

			if (index < this.blocks.length) {
				this.select(this.blocks[index]);
			}
		},
		selectUp() {
			const first = this.selected[0];
			const index = this.findIndex(first) - 1;

			if (index >= 0) {
				this.select(this.blocks[index]);
			}
		},
		selectAll() {
			this.selected = Object.values(this.blocks).map((block) => block.id);
		},
		show(block) {
			set(block, "isHidden", false);
			this.save();
		},
		async sort(block, from, to) {
			if (to < 0) {
				return;
			}
			let blocks = this.$helper.object.clone(this.blocks);
			blocks.splice(from, 1);
			blocks.splice(to, 0, block);
			this.blocks = blocks;
			this.save();
			await this.$nextTick();
			this.focus(block);
		},
		async split(block, index, contents) {
			// prepare old block with reduced content chunk
			const oldBlock = this.$helper.object.clone(block);
			oldBlock.content = { ...oldBlock.content, ...contents[0] };

			// create a new block and merge in default contents as
			// well as the newly splitted content chunk
			const newBlock = await this.$api.get(
				this.endpoints.field + "/fieldsets/" + block.type
			);
			newBlock.content = {
				...newBlock.content,
				...oldBlock.content,
				...contents[1]
			};

			// in one go: remove old block and onsert updated and new block
			this.blocks.splice(index, 1, oldBlock, newBlock);
			this.save();
			await this.$nextTick();
			this.focus(newBlock);
		},
		update(block, content) {
			const index = this.findIndex(block.id);
			if (index !== -1) {
				for (const key in content) {
					set(this.blocks[index].content, key, content[key]);
				}
			}
			this.save();
		}
	}
};
</script>

<style>
:root {
	--block-color-back: var(--item-color-back);
}

.k-blocks {
	border-radius: var(--rounded);
}
.k-blocks:not(:has(> .k-blocks-list:empty), [data-disabled="true"]) {
	background: var(--block-color-back);
	box-shadow: var(--shadow);
}
.k-blocks[data-disabled="true"]:not([data-empty="true"]) {
	border: 1px solid var(--input-color-border);
}
/* When multiselect key is pressed, prevent pointer events on containing blocks, but ensure to reset it for nested blocks */
.k-blocks-list[data-multi-select-key="true"] > .k-block-container * {
	pointer-events: none;
}
.k-blocks-list[data-multi-select-key="true"] > .k-block-container .k-blocks * {
	pointer-events: all;
}
.k-blocks .k-sortable-ghost {
	outline: 2px solid var(--color-focus);
	box-shadow: rgba(17, 17, 17, 0.25) 0 5px 10px;
	cursor: grabbing;
	cursor: -moz-grabbing;
	cursor: -webkit-grabbing;
}

.k-blocks > .k-blocks-empty {
	display: flex;
	align-items: center;
}
.k-blocks > .k-blocks-list:not(:empty) + .k-blocks-empty {
	display: none;
}
</style>
