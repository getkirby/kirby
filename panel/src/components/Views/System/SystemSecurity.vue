<template>
	<k-section
		v-if="issues.length"
		:headline="$t('security')"
		:buttons="[
			{
				title: $t('retry'),
				icon: 'refresh',
				click: retry
			}
		]"
	>
		<k-items
			:items="
				issues.map((issue) => ({
					theme: 'negative',
					image: {
						back: 'var(--theme-color-200)',
						icon: issue.icon ?? 'alert',
						color: 'var(--theme-color-icon)'
					},
					target: '_blank',
					...issue
				}))
			"
		/>
	</k-section>
</template>

<script>
/**
 * @internal
 * @since 4.0.0
 */
export default {
	props: {
		exceptions: Array,
		security: Array,
		urls: Object
	},
	data() {
		return {
			issues: structuredClone(this.security)
		};
	},
	async mounted() {
		console.info(
			"Running system health checks for the Panel system view; failed requests in the following console output are expected behavior."
		);

		// `Promise.all` as fallback for older browsers
		const promiseAll = (Promise.allSettled ?? Promise.all).bind(Promise);

		// call the check method on every URL in the `urls` object
		const promises = Object.entries(this.urls ?? {}).map(this.check);

		await promiseAll([...promises, this.testPatchRequests()]);

		console.info(
			`System health checks ended. ${
				this.issues.length - this.security.length
			} issues with accessible files/folders found (see the security list in the system view).`
		);
	},
	methods: {
		async check([key, url]) {
			if (!url) {
				return;
			}

			const { status } = await fetch(url, { cache: "no-store" });

			if (status < 400) {
				this.issues.push({
					id: key,
					text: this.$t("system.issues." + key),
					link: "https://getkirby.com/security/" + key,
					icon: "folder"
				});
			}
		},
		retry() {
			this.$go(window.location.href);
		},
		/**
		 * Checks if server supports PATH request or if
		 * the `api.methodOverwrite` option needs to be activated
		 */
		async testPatchRequests() {
			const { status } = await this.$api.patch("system/method-test");

			if (status !== "ok") {
				this.issues.push({
					id: "method-overwrite-text",
					text: "Your server does not support PATCH requests",
					link: "https://getkirby.com/docs/reference/system/options/api#methods-overwrite",
					icon: "protected"
				});
			}
		}
	}
};
</script>
