<template>
	<k-box
		v-if="tab.columns.length === 0 && empty"
		:html="true"
		:text="empty"
		theme="info"
	/>

	<k-grid v-else class="k-sections" variant="columns">
		<k-column
			v-for="(column, columnIndex) in tab.columns"
			:key="parent + '-column-' + columnIndex"
			:width="column.width"
			:sticky="column.sticky"
		>
			<template v-for="(section, sectionIndex) in column.sections">
				<template v-if="isVisible(section)">
					<component
						:is="'k-' + section.type + '-section'"
						v-if="exists(section.type)"
						:key="
							parent +
							'-column-' +
							columnIndex +
							'-section-' +
							sectionIndex +
							'-' +
							blueprint
						"
						v-bind="section"
						:column="column.width"
						:content="content"
						:lock="lock"
						:name="section.name"
						:parent="parent"
						:timestamp="$panel.view.timestamp"
						:class="'k-section-name-' + section.name"
						@input="$emit('input', $event)"
						@submit="$emit('submit', $event)"
					/>
					<template v-else>
						<k-box
							:key="
								parent + '-column-' + columnIndex + '-section-' + sectionIndex
							"
							:text="$t('error.section.type.invalid', { type: section.type })"
							icon="alert"
							theme="negative"
						/>
					</template>
				</template>
			</template>
		</k-column>
	</k-grid>
</template>

<script>
export default {
	props: {
		blueprint: String,
		content: Object,
		empty: String,
		lock: [Boolean, Object],
		parent: String,
		tab: Object
	},
	emits: ["input", "submit"],
	methods: {
		exists(type) {
			return this.$helper.isComponent(`k-${type}-section`);
		},
		isVisible(section) {
			return this.$helper.field.isVisible(section, this.content);
		}
	}
};
</script>
