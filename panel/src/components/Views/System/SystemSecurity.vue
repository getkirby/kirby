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
						back: 'light-dark(var(--theme-color-200), var(--theme-color-900))',
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
		exceptions: {
			type: Array,
			default: () => []
		},
		security: {
			type: Array,
			default: () => []
		},
		urls: {
			type: [Object, Array],
			default: () => ({})
		}
	},
	data() {
		return {
			issues: this.$helper.object.clone(this.security)
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

		await promiseAll(promises);

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
		}
	}
};
</script>
