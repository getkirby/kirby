<template>
  <dl class="k-stats">
    <component
      v-for="(report, id) in reports"
      :is="report.link ? 'k-link' : 'div'"
      :data-theme="report.theme"
      :key="id"
      :to="report.link"
      class="k-stat"
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
    reports: Array
  }
};
</script>

<style>
.k-stats {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(10rem, 1fr));
  grid-gap: 1px;
}
.k-stat {
  display: flex;
  flex-direction: column;
  background: var(--color-white);
  filter: drop-shadow(0 1px 3px rgba(0, 0, 0, 0.1));
  padding: var(--spacing-3) var(--spacing-6);
  line-height: var(--leading-normal);
}
.k-stat.k-link:hover {
  background: var(--color-gray-100);
}
.k-stat dt,
.k-stat dd {
  display: block;
}
.k-stat-value {
  font-size: var(--text-2xl);
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
</style>
