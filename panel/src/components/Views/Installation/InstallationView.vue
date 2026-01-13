<template>
	<k-panel-outside class="k-installation-view">
		<h1 class="sr-only">
			{{ $t("installation") }}
		</h1>

		<!-- installation complete -->
		<k-stack v-if="isComplete">
			<k-headline>{{ $t("installation.completed") }}</k-headline>

			<k-button
				:text="$t('login')"
				icon="check"
				link="/login"
				size="lg"
				theme="positive"
				variant="filled"
			/>
		</k-stack>

		<!-- ready to be installed -->
		<form v-else-if="isReady" class="k-stack" @submit.prevent="install">
			<k-fieldset :fields="fields" :value="user" @input="user = $event" />
			<k-button
				:text="$t('install')"
				icon="check"
				size="lg"
				theme="positive"
				type="submit"
				variant="filled"
			/>
		</form>

		<!-- not meeting requirements -->
		<k-stack v-else>
			<k-headline>
				{{ $t("installation.issues.headline") }}
			</k-headline>

			<k-checklist theme="negative" class="k-installation-issues">
				<li v-for="issue in issues" :key="issue">
					<!-- eslint-disable-next-line vue/no-v-html -->
					<span v-html="issue" />
				</li>
			</k-checklist>

			<k-button
				:text="$t('retry')"
				icon="refresh"
				size="lg"
				theme="positive"
				variant="filled"
				@click="$panel.reload"
			/>
		</k-stack>
	</k-panel-outside>
</template>

<script>
/**
 * @internal
 */
export default {
	props: {
		isInstallable: Boolean,
		isInstalled: Boolean,
		isOk: Boolean,
		requirements: Object,
		translations: Array
	},
	data() {
		return {
			user: {
				name: "",
				email: "",
				language: this.$panel.translation.code,
				password: "",
				role: "admin"
			}
		};
	},
	computed: {
		fields() {
			return {
				email: {
					label: this.$t("email"),
					type: "email",
					link: false,
					autofocus: true,
					required: true
				},
				password: {
					label: this.$t("password"),
					type: "password",
					placeholder: this.$t("password") + " …",
					required: true
				},
				language: {
					label: this.$t("language"),
					type: "select",
					options: this.translations,
					icon: "translate",
					empty: false,
					required: true
				}
			};
		},
		isComplete() {
			return this.isOk && this.isInstalled;
		},
		isReady() {
			return this.isOk && this.isInstallable;
		},
		issues() {
			const issues = [];

			if (this.isInstallable === false) {
				issues.push(this.$t("installation.disabled"));
			}

			for (const extension in this.requirements.extensions) {
				if (this.requirements.extensions[extension] === false) {
					issues.push(this.$t("installation.issues.extension", { extension }));
				}
			}

			for (const type of ["accounts", "content", "media", "sessions"]) {
				if (this.requirements[type] === false) {
					issues.push(this.$t("installation.issues." + type));
				}
			}

			return issues;
		}
	},
	methods: {
		async install() {
			try {
				await this.$api.system.install(this.user);
				await this.$panel.reload({
					globals: ["system", "translation"]
				});

				this.$panel.notification.success({
					message: this.$t("welcome") + "!",
					icon: "smile"
				});
			} catch (error) {
				this.$panel.error(error);
			}
		}
	}
};
</script>

<style>
.k-installation-view > .k-stack {
	max-width: 25rem;
	margin: 0 auto;
	gap: var(--spacing-6);
	padding: var(--spacing-6);
	background: light-dark(var(--color-white), var(--color-gray-950));
	border-radius: var(--rounded);
}

.k-installation-issues li code {
	font: inherit;
	color: var(--theme-color-icon-highlight);
}
</style>
