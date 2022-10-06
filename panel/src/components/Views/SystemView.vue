<template>
	<k-inside>
		<k-view class="k-system-view">
			<k-header>
				{{ $t("view.system") }}
			</k-header>
			<section class="k-system-view-section">
				<header class="k-system-view-section-header">
					<k-headline>{{ $t("environment") }}</k-headline>
				</header>

				<k-stats :reports="environment" size="medium" class="k-system-info" />
			</section>

			<section v-if="securityIssues.length" class="k-system-view-section">
				<header class="k-system-view-section-header">
					<k-headline>{{ $t("security") }}</k-headline>
					<k-button :tooltip="$t('retry')" icon="refresh" @click="retry" />
				</header>
				<k-items :items="securityIssues" />
			</section>

			<section v-if="plugins.length" class="k-system-view-section">
				<header class="k-system-view-section-header">
					<k-headline link="https://getkirby.com/plugins">
						{{ $t("plugins") }}
					</k-headline>
				</header>
				<k-table
					:index="false"
					:columns="{
						name: {
							label: $t('name'),
							type: 'url',
							mobile: true
						},
						author: {
							label: $t('author')
						},
						license: {
							label: $t('license')
						},
						version: {
							label: $t('version'),
							type: 'update-status',
							mobile: true,
							width: '10rem'
						}
					}"
					:rows="plugins"
				/>
			</section>
		</k-view>
	</k-inside>
</template>

<script>
export default {
	props: {
		environment: Array,
		exceptions: Array,
		plugins: Array,
		security: Array,
		urls: Object
	},
	data() {
		return {
			accessible: []
		};
	},
	computed: {
		securityIssues() {
			// transform accesible URLs into security messages
			const accessible = this.accessible.map((key) => ({
				id: key,
				text: this.$t("system.issues." + key),
				link: "https://getkirby.com/security/" + key,
				icon: "folder"
			}));

			// merge messages from backend and from dynamic URL checks
			return this.security.concat(accessible).map((issue) => ({
				// give each message an image prop unless it already has one
				image: {
					back: "var(--color-red-200)",
					icon: issue.icon || "alert",
					color: "var(--color-red)"
				},
				...issue
			}));
		}
	},
	async created() {
		// print exceptions from the update check to console for debugging
		if (this.exceptions.length > 0) {
			console.info(
				"The following errors occurred during the update check of Kirby and/or plugins:"
			);
			this.exceptions.map((exception) => console.warn(exception));
			console.info("End of errors from the update check.");
		}

		console.info(
			"Running system health checks for the Panel system view; failed requests in the following console output are expected behavior."
		);

		// `Promise.all` as fallback for older browsers
		const promiseAll = (Promise.allSettled || Promise.all).bind(Promise);

		// call the check method on every URL in the `urls` object
		const promises = Object.entries(this.urls).map(this.check);

		await promiseAll(promises);

		console.info(
			`System health checks ended. ${
				promises.length - this.accessible.length
			} issues found (see the security list in the system view).`
		);
	},
	methods: {
		async check([key, url]) {
			if (!url) {
				return false;
			}

			const result = await this.isAccessible(url);

			if (result === true) {
				this.accessible.push(key);
			}
		},
		async isAccessible(url) {
			const response = await fetch(url, { cache: "no-store" });
			return response.status < 400;
		},
		retry() {
			this.$go(window.location.href);
		}
	}
};
</script>

<style>
.k-system-view .k-header {
	margin-bottom: 1.5rem;
}
.k-system-view-section-header {
	margin-bottom: 0.5rem;
	display: flex;
	justify-content: space-between;
}
.k-system-view-section {
	margin-bottom: 3rem;
}

.k-system-info .k-stat-label {
	color: var(--theme, var(--color-black));
}
</style>
