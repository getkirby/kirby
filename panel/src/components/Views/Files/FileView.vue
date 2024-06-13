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
				<k-button-group>
					<k-button
						:link="preview.url"
						:responsive="true"
						:title="$t('open')"
						class="k-file-view-options"
						icon="open"
						size="sm"
						target="_blank"
						variant="filled"
					/>

					<k-button
						:disabled="isLocked"
						:dropdown="true"
						:title="$t('settings')"
						icon="cog"
						size="sm"
						variant="filled"
						class="k-file-view-options"
						@click="$refs.settings.toggle()"
					/>
					<k-dropdown-content
						ref="settings"
						:options="$dropdown(id)"
						align-x="end"
						@action="action"
					/>

					<k-languages-dropdown />
				</k-button-group>

				<k-form-buttons :lock="lock" />
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
			const focus = this.$store.getters["content/values"]()["focus"];

			if (!focus) {
				return;
			}

			const [x, y] = focus.replaceAll("%", "").split(" ");

			return { x: parseFloat(x), y: parseFloat(y) };
		}
	},
	methods: {
		action(action) {
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

			this.$store.dispatch("content/update", ["focus", focus]);
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
