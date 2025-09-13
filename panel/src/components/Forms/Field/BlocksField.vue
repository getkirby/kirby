<template>
	<k-field
		v-bind="$props"
		:input="id"
		:class="['k-blocks-field', $attrs.class]"
		:style="$attrs.style"
	>
		<template v-if="!disabled && hasFieldsets" #options>
			<k-button-group layout="collapsed">
				<k-button
					:autofocus="autofocus"
					:disabled="isFull"
					:responsive="true"
					:text="$t('add')"
					icon="add"
					variant="filled"
					size="xs"
					class="input-focus"
					@click="$refs.blocks.choose(value.length)"
				/>
				<k-button
					icon="dots"
					variant="filled"
					size="xs"
					@click="$refs.options.toggle()"
				/>
				<k-dropdown ref="options" :options="options" align-x="end" />
			</k-button-group>
		</template>

		<k-input-validator
			v-bind="{ id, min, max, required }"
			:value="JSON.stringify(value)"
		>
			<k-blocks
				ref="blocks"
				v-bind="$props"
				@close="opened = $event"
				@open="opened = $event"
				@input="$emit('input', $event)"
			/>
		</k-input-validator>

		<footer v-if="!disabled && !isEmpty && !isFull && hasFieldsets">
			<k-button
				:title="$t('add')"
				icon="add"
				size="xs"
				variant="filled"
				@click="$refs.blocks.choose(value.length)"
			/>
		</footer>
	</k-field>
</template>

<script>
import { props as FieldProps } from "../Field.vue";
import { props as BlocksProps } from "@/components/Forms/Blocks/Blocks.vue";

export default {
	mixins: [FieldProps, BlocksProps],
	inheritAttrs: false,
	emits: ["input"],
	data() {
		return {
			opened: []
		};
	},
	computed: {
		hasFieldsets() {
			return this.$helper.object.length(this.fieldsets) > 0;
		},
		isEmpty() {
			return this.value.length === 0;
		},
		isFull() {
			return this.max && this.value.length >= this.max;
		}
	},
	methods: {
		focus() {
			this.$refs.blocks.focus();
		},
		options(ready) {
			const options = [
				{
					click: () => this.$refs.blocks.copyAll(),
					disabled: this.isEmpty,
					icon: "template",
					text: this.$t("copy.all")
				},
				{
					click: () => this.$refs.blocks.pasteboard(),
					disabled: this.isFull,
					icon: "download",
					text: this.$t("paste")
				}
			];

			if (
				this.$refs.blocks.isCollapsible() === true ||
				this.$refs.blocks.isExpandable() === true
			) {
				options.push("-");
			}

			if (this.$refs.blocks.isCollapsible() === true) {
				options.push({
					click: () => this.$refs.blocks.collapseAll(),
					disabled: this.isEmpty || this.$refs.blocks.isFullyCollapsed(),
					icon: "collapse",
					text: this.$t("collapse.all")
				});
			}

			if (this.$refs.blocks.isExpandable() === true) {
				options.push({
					click: () => this.$refs.blocks.expandAll(),
					disabled: this.isEmpty || this.$refs.blocks.isFullyExpanded(),
					icon: "expand",
					text: this.$t("expand.all")
				});
			}

			options.push("-");
			options.push({
				click: () => this.$refs.blocks.removeAll(),
				disabled: this.isEmpty,
				icon: "trash",
				text: this.$t("delete.all")
			});

			return ready(options);
		}
	}
};
</script>

<style>
.k-blocks-field {
	position: relative;
}

.k-blocks-field > footer {
	display: flex;
	justify-content: center;
	margin-top: var(--spacing-3);
}
</style>
