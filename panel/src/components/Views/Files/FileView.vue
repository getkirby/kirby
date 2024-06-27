<template>
	<k-panel-inside
		:data-has-tabs="tabs.length > 1"
		:data-id="model.id"
		:data-locked="isLocked"
		:data-template="blueprint"
		class="k-file-view"
	>
		<template #topbar>
			<k-prev-next :prev="prev" :next="next" />
		</template>

		<k-header
			:editable="permissions.changeName && !isLocked"
			class="k-file-view-header"
			@edit="$dialog(id + '/changeName')"
		>
			{{ model.filename }}

			<template #buttons>
				<k-view-buttons :buttons="buttons" @action="onAction" />
				<k-form-buttons />
			</template>
		</k-header>

		<k-file-preview v-bind="preview" :focus="focus" @focus="setFocus" />

		<k-model-tabs :tab="tab.name" :tabs="tabs" />

		<k-sections
			:blueprint="blueprint"
			:empty="$t('file.blueprint', { blueprint: $esc(blueprint) })"
			:lock="lock"
			:parent="id"
			:tab="tab"
		/>
	</k-panel-inside>
</template>

<script>
import ModelView from "../ModelView.vue";

export default {
	extends: ModelView,
	props: {
		preview: Object
	},
	computed: {
		focus() {
			const focus = this.$content.values["focus"];

			if (!focus) {
				return;
			}

			const [x, y] = focus.replaceAll("%", "").split(" ");

			return { x: parseFloat(x), y: parseFloat(y) };
		}
	},
	methods: {
		onAction(action) {
			switch (action) {
				case "replace":
					return this.$panel.upload.replace({
						...this.preview,
						...this.model
					});
			}
		},
		setFocus(focus) {
			if (this.$helper.object.isObject(focus) === true) {
				focus = `${focus.x}% ${focus.y}%`;
			}

			this.$content.set("focus", focus);
		}
	}
};
</script>

<style>
.k-file-view-header {
	margin-bottom: 0;
}

/** TODO: .k-file-view:has(.k-tabs) .k-file-preview  */
.k-file-view[data-has-tabs="true"] .k-file-preview {
	margin-bottom: 0;
}
</style>
