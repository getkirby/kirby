<template>
	<component :is="component" :data-theme="theme" :to="target" class="k-stat">
		<dt v-if="label" class="k-stat-label">
			<k-icon v-if="icon" :type="icon" />

			{{ label }}
		</dt>
		<dd v-if="value" class="k-stat-value">{{ value }}</dd>
		<dd v-if="info" class="k-stat-info">{{ info }}</dd>
	</component>
</template>

<script>
/**
 * Single stat report used in `k-stats`
 * @since 4.0.0
 *
 * @example <k-stat label="Pages" value="123" info="Last week" theme="info" />
 */
export default {
	props: {
		/**
		 * Label text of the stat (2nd line)
		 */
		label: String,
		/**
		 * Main text of the stat (1st line)
		 */
		value: String,
		/**
		 * Additional icon for the stat
		 * @since 4.0.0
		 */
		icon: String,
		/**
		 * Additional text of the stat (3rd line)
		 */
		info: String,
		/**
		 * @values "negative", "positive", "warning", "info"
		 */
		theme: String,
		/** Absolute or relative URL */
		link: String,
		/**
		 * Function to be called when clicked
		 */
		click: Function,
		/**
		 * Dialog endpoint or options to be passed to `this.$dialog`
		 */
		dialog: {
			type: [String, Object]
		}
	},
	computed: {
		component() {
			if (this.target !== null) {
				return "k-link";
			}

			return "div";
		},
		target() {
			if (this.link) {
				return this.link;
			}

			if (this.click) {
				return this.click;
			}

			if (this.dialog) {
				return () => this.$dialog(this.dialog);
			}

			return null;
		}
	}
};
</script>

<style>
:root {
	--stat-color-back: var(--item-color-back);
	--stat-color-hover-back: light-dark(
		var(--color-gray-100),
		var(--color-gray-850)
	);
	--stat-info-text-color: var(--color-text-dimmed);
	--stat-value-text-size: var(--text-2xl);
}

.k-stat {
	display: flex;
	flex-direction: column;
	padding: var(--spacing-3) var(--spacing-6);
	background: var(--stat-color-back);
	border-radius: var(--rounded);
	box-shadow: var(--shadow);
	line-height: var(--leading-normal);
}
.k-stat.k-link:hover {
	cursor: pointer;
	background: var(--stat-color-hover-back);
}
.k-stat :where(dt, dd) {
	display: block;
}
.k-stat-value {
	order: 1;
	font-size: var(--stat-value-text-size);
	margin-bottom: var(--spacing-1);
}
.k-stat-label {
	--icon-size: var(--text-sm);

	order: 2;
	display: flex;
	justify-content: start;
	align-items: center;
	gap: var(--spacing-1);
	font-size: var(--text-xs);
}
.k-stat-info {
	order: 3;
	font-size: var(--text-xs);
	color: var(--stat-info-text-color);
}
.k-stat:is([data-theme]) .k-stat-info {
	--stat-info-text-color: var(--theme-color-text);
}
</style>
