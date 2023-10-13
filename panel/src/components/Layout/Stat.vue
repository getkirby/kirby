<template>
	<component :is="component" :data-theme="theme" :to="target" class="k-stat">
		<dt v-if="label" class="k-stat-label">{{ label }}</dt>
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
		label: {
			type: String
		},
		/**
		 * Main text of the stat (1st line)
		 */
		value: {
			type: String
		},
		/**
		 * Additional text of the stat (3rd line)
		 */
		info: {
			type: String
		},
		/**
		 * @values "negative", "positive", "warning", "info"
		 */
		theme: {
			type: String
		},
		/** Absolute or relative URL */
		link: {
			type: String
		},
		/**
		 * Function to be called when clicked
		 */
		click: {
			type: Function
		},
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
.k-stat {
	display: flex;
	flex-direction: column;
	background: var(--color-white);
	box-shadow: var(--shadow);
	padding: var(--spacing-3) var(--spacing-6);
	line-height: var(--leading-normal);
	border-radius: var(--rounded);
}
.k-stat.k-link:hover {
	cursor: pointer;
	background: var(--color-gray-100);
}
.k-stat :where(dt, dd) {
	display: block;
}
.k-stat-value {
	font-size: var(--stat-value-text-size, var(--text-2xl));
	margin-bottom: var(--spacing-1);
	order: 1;
}
.k-stat-label {
	font-size: var(--text-xs);
	order: 2;
}
.k-stat-info {
	font-size: var(--text-xs);
	color: var(--theme-color-700, var(--color-text-dimmed));
	order: 3;
}
</style>
