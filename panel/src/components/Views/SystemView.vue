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

			<section v-if="security.length" class="k-system-view-section">
				<header class="k-system-view-section-header">
					<k-headline>{{ $t("security") }}</k-headline>
					<k-button :tooltip="$t('retry')" icon="refresh" @click="retry" />
				</header>
				<k-items :items="security" />
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
							width: '8rem',
							mobile: true
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
		debug: Boolean,
		license: String,
		php: String,
		plugins: Array,
		server: String,
		https: Boolean,
		urls: Object,
		version: String
	},
	data() {
		return {
			security: []
		};
	},
	computed: {
		environment() {
			return [
				{
					label: this.$t("license"),
					value: this.license
						? "Kirby 3"
						: this.$t("license.unregistered.label"),
					theme: this.license ? null : "negative",
					click: this.license
						? () => this.$dialog("license")
						: () => this.$dialog("registration")
				},
				{
					label: this.$t("version"),
					value: this.version,
					link: "https://github.com/getkirby/kirby/releases/tag/" + this.version
				},
				{
					label: "PHP",
					value: this.php
				},
				{
					label: this.$t("server"),
					value: this.server || "?"
				}
			];
		}
	},
	async created() {
		console.info(
			"Running system health checks for the Panel system view; failed requests in the following console output are expected behavior."
		);

		// `Promise.all` as fallback for older browsers
		let promiseAll = (Promise.allSettled || Promise.all).bind(Promise);

		await promiseAll([
			this.check("content"),
			this.check("debug"),
			this.check("git"),
			this.check("https"),
			this.check("kirby"),
			this.check("site")
		]);

		console.info("System health checks ended.");
	},
	methods: {
		async check(key) {
			switch (key) {
				case "debug":
					if (this.debug === true) {
						this.securityIssue(key);
					}
					break;
				case "https":
					if (this.https !== true) {
						this.securityIssue(key);
					}
					break;
				default: {
					const url = this.urls[key];

					if (!url) {
						return false;
					}

					if ((await this.isAccessible(url)) === true) {
						this.securityIssue(key);
					}
				}
			}
		},
		securityIssue(key) {
			this.security.push({
				image: {
					back: "var(--color-red-200)",
					icon: "alert",
					color: "var(--color-red)"
				},
				id: key,
				text: this.$t("system.issues." + key),
				link: "https://getkirby.com/security/" + key
			});
		},
		async isAccessible(url) {
			const response = await fetch(url, {
				cache: "no-store"
			});

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
.k-system-info [data-theme] .k-stat-value {
	color: var(--theme);
}
</style>
