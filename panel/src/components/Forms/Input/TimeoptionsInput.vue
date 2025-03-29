<template>
	<div :class="['k-timeoptions-input', $attrs.class]" :style="$attrs.style">
		<div>
			<h3>
				<k-icon type="sun" /> <span class="sr-only">{{ $t("day") }}</span>
			</h3>
			<ul>
				<li v-for="(time, index) in day" :key="time.select">
					<hr v-if="time === '-'" />
					<k-button
						v-else
						:autofocus="autofocus && index === 0"
						:disabled="disabled"
						:selected="time.select === value ? 'time' : false"
						@click="select(time.select)"
					>
						{{ time.display }}
					</k-button>
				</li>
			</ul>
		</div>
		<div>
			<h3>
				<k-icon type="moon" /> <span class="sr-only">{{ $t("night") }}</span>
			</h3>
			<ul>
				<li v-for="time in night" :key="time.select">
					<hr v-if="time === '-'" />
					<k-button
						v-else
						:disabled="disabled"
						:selected="time.select === value ? 'time' : false"
						@click="select(time.select)"
					>
						{{ time.display }}
					</k-button>
				</li>
			</ul>
		</div>

		<!-- Hidden input for validation -->
		<input
			:id="id"
			:disabled="disabled"
			:formnovalidate="novalidate"
			:min="min"
			:max="max"
			:name="name"
			:required="required"
			:value="value"
			class="input-hidden"
			tabindex="-1"
			type="time"
		/>
	</div>
</template>

<script>
import Input, { props as InputProps } from "@/mixins/input.js";
import { IsoTimeProps } from "./TimeInput.vue";

export const props = {
	mixins: [InputProps, IsoTimeProps]
};

/**
 * The Times component displayes available times to choose from
 * @since 4.0.0
 * @example <k-timeoptions-input value="12:12" @input="onInput" />
 */
export default {
	mixins: [Input, props],
	props: {
		display: {
			type: String,
			default: "HH:mm"
		},
		value: String
	},
	computed: {
		day() {
			return this.formatTimes([
				6,
				7,
				8,
				9,
				10,
				11,
				"-",
				12,
				13,
				14,
				15,
				16,
				17
			]);
		},
		night() {
			return this.formatTimes([18, 19, 20, 21, 22, 23, "-", 0, 1, 2, 3, 4, 5]);
		}
	},
	methods: {
		focus() {
			this.$el.querySelector("button").focus();
		},
		formatTimes(times) {
			return times.map((time) => {
				if (time === "-") {
					return time;
				}

				const dt = this.$library.dayjs(time + ":00", "H:mm");
				return {
					display: dt.format(this.display),
					select: dt.toISO("time")
				};
			});
		},
		select(time) {
			this.$emit("input", time);
		}
	}
};
</script>

<style>
.k-timeoptions-input {
	--button-height: var(--height-sm);
	display: grid;
	grid-template-columns: 1fr 1fr;
	gap: var(--spacing-3);
}
.k-timeoptions-input h3 {
	display: flex;
	align-items: center;
	padding-inline: var(--button-padding);
	height: var(--button-height);
	margin-bottom: var(--spacing-1);
}
.k-timeoptions-input hr {
	margin: var(--spacing-2) var(--spacing-3);
}
.k-timeoptions-input .k-button[aria-selected="time"] {
	--button-color-text: var(--color-text);
	--button-color-back: var(--color-blue-500);
}
</style>
