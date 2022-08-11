<template>
	<dl class="k-stats" :data-size="size">
		<component
			:is="report.link ? 'k-link' : 'div'"
			v-for="(report, id) in reports"
			:key="id"
			:data-theme="report.theme"
			:data-click="!!report.click"
			:to="report.link"
			class="k-stat"
			@click="report.click ? report.click() : null"
		>
			<dt class="k-stat-label">{{ report.label }}</dt>
			<dd class="k-stat-value">{{ report.value }}</dd>
			<dd class="k-stat-info">{{ report.info }}</dd>
		</component>
	</dl>
</template>

<script>
export default {
	props: {
		reports: Array,
		size: {
			type: String,
			default: "large"
		}
	}
};
</script>

<style>
.k-stats {
	display: grid;
	grid-template-columns: repeat(auto-fit, minmax(14rem, 1fr));
	grid-gap: var(--spacing-2px);
}
.k-stat {
	display: flex;
	flex-direction: column;
	background: var(--color-white);
	box-shadow: var(--shadow);
	padding: var(--spacing-3) var(--spacing-6);
	line-height: var(--leading-normal);
	border-radius: var(--rounded);
}
.k-stat.k-link:hover,
.k-stat[data-click="true"]:hover {
	cursor: pointer;
	background: var(--color-gray-100);
}
.k-stat dt,
.k-stat dd {
	display: block;
}
.k-stat-value {
	font-size: var(--value);
	margin-bottom: var(--spacing-1);
	order: 1;
}
.k-stat-label,
.k-stat-info {
	font-size: var(--text-xs);
}
.k-stat-label {
	order: 2;
}
.k-stat-info {
	order: 3;
	color: var(--theme, var(--color-gray-500));
}
.k-stats[data-size="small"] {
	--value: var(--text-base);
}
.k-stats[data-size="medium"] {
	--value: var(--text-xl);
}
.k-stats[data-size="large"] {
	--value: var(--text-2xl);
}
.k-stats[data-size="huge"] {
	--value: var(--text-3xl);
}
</style>
