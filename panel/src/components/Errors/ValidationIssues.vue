<template>
	<k-definitions class="k-validation-issues">
		<k-definition
			v-for="(field, fieldName) in fields"
			:key="fieldName"
			:term="field.label"
		>
			<k-checklist theme="negative">
				<li
					v-for="(issue, issueKey) in field.issues"
					:key="fieldName + '-issue-' + issueKey"
				>
					{{ issue }}
				</li>
			</k-checklist>
		</k-definition>
	</k-definitions>
</template>

<script>
/**
 * @since 6.0.0
 */
export default {
	props: {
		fields: Object
	}
};
</script>

<style>
.k-validation-issues {
	--definition-term-width: 33%;
	--definition-height: auto;
	--definition-align: start;
}
.k-validation-issues dt {
	line-height: var(--text-line-height);
}
</style>
